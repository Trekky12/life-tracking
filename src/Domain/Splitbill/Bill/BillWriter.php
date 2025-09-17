<?php

namespace App\Domain\Splitbill\Bill;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountService;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\User\UserService;
use App\Domain\Finances\FinancesService;
use App\Domain\Finances\FinancesWriter;
use App\Domain\Finances\FinancesRemover;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Finances\Transaction\TransactionMapper;
use App\Domain\Finances\Transaction\TransactionRemover;
use App\Domain\Finances\Transaction\TransactionWriter;
use App\Domain\Main\Translator;
use App\Domain\Splitbill\BaseBillWriter;
use App\Domain\Finances\Paymethod\PaymethodService;

class BillWriter extends BaseBillWriter {

    protected $user_service;
    protected $bill_notification_service;
    protected $finance_service;
    protected $finances_entry_writer;
    protected $finances_entry_remover;
    protected $finance_mapper;
    protected $transaction_writer;
    protected $transaction_remover;
    protected $transaction_mapper;
    protected $paymethod_service;
    protected $account_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        SplitbillGroupService $group_service,
        GroupMapper $group_mapper,
        Translator $translation,
        BillMapper $mapper,
        FinancesService $finance_service,
        FinancesWriter $finances_entry_writer,
        FinancesRemover $finances_entry_remover,
        FinancesMapper $finance_mapper,
        BillNotificationService $bill_notification_service,
        UserService $user_service,
        SplitbillBillService $service,
        TransactionWriter $transaction_writer,
        TransactionRemover $transaction_remover,
        TransactionMapper $transaction_mapper,
        PaymethodService $paymethod_service,
        AccountService $account_service
    ) {
        parent::__construct($logger, $user, $activity, $group_service, $group_mapper, $translation);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->bill_notification_service = $bill_notification_service;
        $this->finance_service = $finance_service;
        $this->finances_entry_writer = $finances_entry_writer;
        $this->finances_entry_remover = $finances_entry_remover;
        $this->finance_mapper = $finance_mapper;
        $this->service = $service;
        $this->transaction_writer = $transaction_writer;
        $this->transaction_remover = $transaction_remover;
        $this->transaction_mapper = $transaction_mapper;
        $this->paymethod_service = $paymethod_service;
        $this->account_service = $account_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $users_preSave = $this->mapper->getBillUsers($id);

        $payload = parent::save($id, $data, $additionalData);

        if ($payload->getStatus() == Payload::$NO_ACCESS) {
            return $payload;
        }

        // Update finance entries and transactions when paymethod is changed
        if ($payload->getStatus() == Payload::$STATUS_UPDATE_PAYMETHOD) {
            $balance = $payload->getResult();
            $bill = $this->service->getEntry($id);
            $user_id = $this->current_user->getUser()->id;

            if ($bill->settleup != 1) {

                $finance_entry = $this->finance_mapper->getEntryFromBill($user_id, $bill->id);

                if ($finance_entry) {
                    // Update finance entry -> this triggers the transaction logic
                    $data = $finance_entry->copy();
                    $data["id"] = $finance_entry->id;
                    $data["paymethod"] = $balance["paymethod"];
                    $this->finances_entry_writer->save($data["id"], $data, ["is_bill_based_save" => true, "use_user_from_data" => true]);
                } else {
                    // Splitted bill was paid by the user but not spend by him? => Create only transaction
                    $this->createTransaction($balance, $bill);
                }
            } else {
                // Create settle up transaction
                $this->createTransaction($balance, $bill);
                $this->createSettleUpIncomingTransaction($balance, $bill);
            }
        }

        if (in_array($payload->getStatus(), [Payload::$STATUS_NEW, Payload::$STATUS_UPDATE, Payload::$STATUS_NO_UPDATE])) {

            $bill = $payload->getResult();

            $group = $this->group_service->getFromHash($additionalData["group"]);
            $users = $this->user_service->getAll();
            $balances = $this->getMapper()->getBalance($bill->id);
            $totalValue = $this->getMapper()->getBillSpend($bill->id);

            $me = $this->current_user->getUser();

            foreach ($balances as $balance) {
                /**
                 * Create Finance Entry for User
                 */
                $userObj = $users[$balance["user"]];

                if ($group->add_finances > 0 && $userObj->module_finance == 1) {

                    // Before creating/deleting a finance entry the user is checked
                    // for access rights, but when an entry for another user is created the 
                    // current user has no access rights
                    // So the current user needs to be set to the
                    // recurring splitted bill creator
                    $this->current_user->setUser(null);
                    $this->finance_mapper->setUser($balance["user"]);
                    $this->transaction_mapper->setUser($balance["user"]);

                    if ($bill->settleup != 1) {

                        $finance_entry = $this->finance_mapper->getEntryFromBill($balance["user"], $bill->id);

                        if ($balance["spend"] > 0) {

                            $data = [
                                "id" => null,
                                "date" => $bill->date,
                                "time" => $bill->time,
                                "description" => $bill->name,
                                "type" => 0,
                                "value" => $balance["spend"],
                                "user" => $balance["user"],
                                "common" => 1,
                                "common_value" => $totalValue,
                                "bill" => $bill->id,
                                "bill_paid" => $balance["paid"],
                                "bill_paid_foreign" => $balance["paid_foreign"],
                                "lng" => $bill->lng,
                                "lat" => $bill->lat,
                                "acc" => $bill->acc,
                                "paymethod" => $balance["paymethod"]
                            ];

                            if (!is_null($finance_entry)) {
                                $data["id"] = $finance_entry->id;
                                $data["description"] = $finance_entry->description;
                            }

                            $this->finances_entry_writer->save($data["id"], $data, ["is_bill_based_save" => true, "use_user_from_data" => true]);
                        } else {

                            // Entry was updated and the user has no longer "paid" something? => delete the corresponding finance entry of the user
                            if (!is_null($finance_entry)) {
                                $this->finances_entry_remover->delete($finance_entry->id, ["is_bill_based_delete" => true]);
                            }

                            // Splitted bill was paid by the user but not spend by him? => Create only transaction
                            $this->createTransaction($balance, $bill);
                        }
                    } else {
                        // Create settle up transaction
                        $this->createTransaction($balance, $bill);
                        $this->createSettleUpIncomingTransaction($balance, $bill);
                    }
                }
            }

            // Reset user back to initial!
            if (!is_null($me)) {
                $this->current_user->setUser($me);
                $this->finance_mapper->setUser($me->id);
                $this->transaction_mapper->setUser($me->id);
            }

            /**
             * Notify Users
             */
            $is_new_bill = $payload->getStatus() == Payload::$STATUS_NEW;
            $this->bill_notification_service->notifyUsers("edit", $bill, $group, $is_new_bill, $users_preSave);
        }
        return $payload;
    }

    public function getParentMapper() {
        return $this->group_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'splitbill_bills';
    }

    public function getObjectViewRouteParams($entry): array {
        $group = $this->getParentMapper()->get($entry->getParentID());
        return [
            "group" => $group->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "splitbills";
    }

    private function createTransaction($balance, $bill) {
        $transaction_entry = $this->transaction_mapper->getEntryFromBill($balance["user"], $bill->id, 0, 0);
        $transaction_entry_round_up_savings = $this->transaction_mapper->getEntryFromBill($balance["user"], $bill->id, 1, 0);
        $transaction_entry_exchange_fee = $this->transaction_mapper->getEntryFromBill($balance["user"], $bill->id, 0, 1);

        $paymethod = $this->paymethod_service->getPaymethodOfUser($balance["paymethod"], $balance["user"]);
        $value = $balance["paid"];
        $exchange_fee = 0;

        if ($value > 0 && !is_null($paymethod) && !is_null($paymethod->account)) {

            /**
             * make a separate transaction for the bill and for the 
             * exchange fee, for round-up savings the exchange fee is not needed
             */
            if (!is_null($balance["paid_foreign"])) {
                $bill_paid = $balance["paid_foreign"] * (float)$bill->exchange_rate;
                $exchange_fee = number_format($bill_paid * ((float)$bill->exchange_fee / 100), 2);

                $value = number_format($bill_paid, 2);
            }

            $data = [
                "id" => null,
                "date" => $bill->date,
                "time" => $bill->time,
                "description" => $bill->name,
                "value" => $value,
                "user" => $balance["user"],
                "bill_entry" => $bill->id,
                "account_from" => $paymethod->account,
                "account_to" => null,
                "is_round_up_savings" => 0,
                "is_exchange_fee" => 0
            ];

            if (!is_null($transaction_entry)) {
                $data["id"] = $transaction_entry->id;
                $data["description"] = $transaction_entry->description;
            }

            $this->transaction_writer->save($data["id"], $data, ["is_bill_based_save" => true, "use_user_from_data" => true]);

            /**
             * Exchange fee transaction
             */
            if ($exchange_fee > 0) {

                $data2 = [
                    "id" => null,
                    "date" => $bill->date,
                    "time" => $bill->time,
                    "description" => sprintf("%s %s", $this->translation->getTranslatedString("EXCHANGE_FEE"), $bill->name),
                    "value" => $exchange_fee,
                    "user" => $balance["user"],
                    "bill_entry" => $bill->id,
                    "account_from" => $paymethod->account,
                    "account_to" => null,
                    "is_round_up_savings" => 0,
                    "is_exchange_fee" => 1
                ];

                if (!is_null($transaction_entry_exchange_fee)) {
                    $data["id"] = $transaction_entry_exchange_fee->id;
                    $data["description"] = $transaction_entry_exchange_fee->description;
                }

                $this->transaction_writer->save($data2["id"], $data2, ["is_bill_based_save" => true, "use_user_from_data" => true]);
            }

            /**
             * Round up savings
             */
            if (!is_null($paymethod->round_up_savings_account) && $paymethod->round_up_savings > 0) {

                $saving = (ceil($value / $paymethod->round_up_savings) * $paymethod->round_up_savings) - $value;

                if ($saving > 0) {
                    $data3 = [
                        "id" => null,
                        "date" => $bill->date,
                        "time" => $bill->time,
                        "description" => sprintf("%s %s", $this->translation->getTranslatedString("FINANCES_ROUND_UP_SAVINGS"), $bill->name),
                        "value" => $saving,
                        "user" => $balance["user"],
                        "bill_entry" => $bill->id,
                        "account_from" => $paymethod->account,
                        "account_to" => $paymethod->round_up_savings_account,
                        "is_round_up_savings" => 1,
                        "is_exchange_fee" => 0
                    ];

                    if (!is_null($transaction_entry_round_up_savings)) {
                        $data3["id"] = $transaction_entry_round_up_savings->id;
                        $data3["description"] = $transaction_entry_round_up_savings->description;
                    }

                    $this->transaction_writer->save($data3["id"], $data3, ["is_bill_based_save" => true, "use_user_from_data" => true]);
                }
            }
        } else {
            if (!is_null($transaction_entry)) {
                $this->transaction_remover->delete($transaction_entry->id, ["is_bill_based_delete" => true]);
            }

            if (!is_null($transaction_entry_round_up_savings)) {
                $this->transaction_remover->delete($transaction_entry_round_up_savings->id, ["is_bill_based_delete" => true]);
            }

            if (!is_null($transaction_entry_exchange_fee)) {
                $this->transaction_remover->delete($transaction_entry_exchange_fee->id, ["is_bill_based_delete" => true]);
            }
        }
    }

    private function createSettleUpIncomingTransaction($balance, $bill) {
        $transaction_entry = $this->transaction_mapper->getEntryFromBill($balance["user"], $bill->id, 0, true);

        $account_to = $this->account_service->getAccountOfUser($balance["account_to"], $balance["user"]);
        $value = $balance["spend"];

        if ($value > 0 && !is_null($account_to)) {
            $data = [
                "id" => null,
                "date" => $bill->date,
                "time" => $bill->time,
                "description" => $bill->name,
                "value" => $value,
                "user" => $balance["user"],
                "bill_entry" => $bill->id,
                "account_from" => null,
                "account_to" => $account_to->id,
                "is_round_up_savings" => 0,
                "is_exchange_fee" => 0
            ];

            if (!is_null($transaction_entry)) {
                $data["id"] = $transaction_entry->id;
                $data["description"] = $transaction_entry->description;
            }

            $this->transaction_writer->save($data["id"], $data, ["is_bill_based_save" => true, "use_user_from_data" => true]);
        } else {
            if (!is_null($transaction_entry)) {
                $this->transaction_remover->delete($transaction_entry->id, ["is_bill_based_delete" => true]);
            }
        }
    }
}
