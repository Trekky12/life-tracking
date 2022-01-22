<?php

namespace App\Domain\Splitbill\Bill;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
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

class BillWriter extends BaseBillWriter
{

    protected $user_service;
    protected $bill_notification_service;
    protected $finance_service;
    protected $finances_entry_writer;
    protected $finances_entry_remover;
    protected $finance_mapper;
    protected $transaction_writer;
    protected $transaction_remover;
    protected $transaction_mapper;

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
        TransactionMapper $transaction_mapper
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
    }

    public function save($id, $data, $additionalData = null): Payload
    {
        $payload = parent::save($id, $data, $additionalData);
        $bill = $payload->getResult();

        if ($payload->getStatus() == Payload::$NO_ACCESS) {
            return $payload;
        }

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
                            "lng" => $bill->lng,
                            "lat" => $bill->lat,
                            "acc" => $bill->acc,
                            "paymethod" => $balance["paymethod"]
                        ];

                        if (!is_null($finance_entry)) {
                            $data["id"] = $finance_entry->id;
                            $data["description"] = $finance_entry->description;
                        }

                        $this->finances_entry_writer->save($data["id"], $data, ["is_bill_based_save" => true]);
                    } else {
                        if (!is_null($finance_entry)) {
                            $this->finances_entry_remover->delete($finance_entry->id, ["is_bill_based_delete" => true]);
                        }
                    }
                } else {
                    $transaction_entry = $this->transaction_mapper->getEntryFromBill($balance["user"], $bill->id);

                    if ($balance["paid"] > 0) {
                        $data = [
                            "id" => null,
                            "date" => $bill->date,
                            "time" => $bill->time,
                            "description" => $bill->name,
                            "value" => $balance["paid"],
                            "user" => $balance["user"],
                            "bill_entry" => $bill->id,
                            "account_from" => $balance["paymethod"],
                            "account_to" => null
                        ];

                        if (!is_null($transaction_entry)) {
                            $data["id"] = $transaction_entry->id;
                            $data["description"] = $transaction_entry->description;
                        }

                        $this->transaction_writer->save($data["id"], $data, ["is_bill_based_save" => true]);
                    } else {
                        if (!is_null($transaction_entry)) {
                            $this->transaction_remover->delete($transaction_entry->id, ["is_bill_based_delete" => true]);
                        }
                    }
                }
            }
        }

        // Reset user back to initial!
        $this->current_user->setUser($me);
        $this->finance_mapper->setUser($me->id);
        $this->transaction_mapper->setUser($me->id);

        /**
         * Notify Users
         */
        $is_new_bill = $payload->getStatus() == Payload::$STATUS_NEW;
        $this->bill_notification_service->notifyUsers("edit", $bill, $group, $is_new_bill);

        return $payload;
    }

    public function getParentMapper()
    {
        return $this->group_mapper;
    }

    public function getObjectViewRoute(): string
    {
        return 'splitbill_bills';
    }

    public function getObjectViewRouteParams($entry): array
    {
        $group = $this->getParentMapper()->get($entry->getParentID());
        return [
            "group" => $group->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string
    {
        return "splitbills";
    }
}
