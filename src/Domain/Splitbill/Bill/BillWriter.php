<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\User\UserService;
use App\Domain\Finances\FinancesService;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Main\Translator;

class BillWriter extends ObjectActivityWriter {

    private $service;
    private $group_service;
    private $group_mapper;
    private $bill_notification_service;
    private $user_service;
    private $finance_service;
    private $translation;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            BillMapper $mapper,
            SplitbillBillService $service,
            SplitbillGroupService $group_service,
            GroupMapper $group_mapper,
            BillNotificationService $bill_notification_service,
            UserService $user_service,
            FinancesService $finance_service,
            Translator $translation) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->group_service = $group_service;
        $this->group_mapper = $group_mapper;
        $this->bill_notification_service = $bill_notification_service;
        $this->user_service = $user_service;
        $this->finance_service = $finance_service;
        $this->translation = $translation;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $group = $this->group_service->getFromHash($additionalData["group"]);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['sbgroup'] = $group->id;

        $payload = parent::save($id, $data, $additionalData);
        $bill = $payload->getResult();

        /**
         * Save Balance
         */
        list($existing_balance, $totalValue, $totalValueForeign) = $this->service->getBillbalance($bill->id);

        if (array_key_exists("balance", $data) && is_array($data["balance"])) {
            $splitbill_groups_users = $this->group_service->getUsers($bill->sbgroup);
            $add_balance = $this->addBalances($bill, $group, $splitbill_groups_users, $data);

            // Balance was wrong!
            // delete success message of bill
            if (!$add_balance) {
                // add error message
                $bill->addParsingError($this->translation->getTranslatedString("SPLITBILLS_BILL_ERROR"));
                return new Payload(Payload::$STATUS_PARSING_ERRORS, $bill);
            }
        }

        /**
         * Notify Users
         */
        $this->bill_notification_service->notifyUsers("edit", $bill, $group, $existing_balance);

        return $payload;
    }

    public function addBalances($bill, $sbgroup, $splitbill_groups_users, $data) {

        $users = $this->user_service->getAll();
        $existing_balance = $this->mapper->getBalance($bill->id);

        $removed_users = array_diff(array_keys($existing_balance), $splitbill_groups_users);

        list($balances, $sum_paid, $sum_spend, $totalValue, $totalValueForeign) = $this->filterBalances($data, $splitbill_groups_users);

        // floating point comparison
        if (!empty($balances) && $totalValue > 0 && (abs(($totalValue - $sum_paid) / $totalValue) < 0.00001) && (abs(($totalValue - $sum_spend) / $totalValue) < 0.00001)) {
            $this->logger->addInfo('Add balance for bill', array("bill" => $bill->id, "balances" => $balances));

            $this->addBalancesForUsers($bill, $sbgroup, $balances, $totalValue, $users);

            // delete entries for users removed from the group
            foreach ($removed_users as $ru) {
                $this->mapper->deleteBalanceofUser($bill->id, $ru);
            }
        } else if ($totalValue > 0) {
            $this->logger->addError('Balance for bill wrong', array("bill" => $bill, "data" => $data));

            // there was an error with the balance, so delete the bill
            $has_balance = count($existing_balance) > 0;
            // delete the bill only when there are no existing balance entries (new bill)
            if (!$has_balance) {
                $this->logger->addWarning('delete bill', array("bill" => $bill, "data" => $data));
                $this->mapper->delete($bill->id);
            }

            return false;
        }

        return true;
    }

    private function filterBalances($data, $group_users) {
        $totalValue = array_key_exists("value", $data) ? floatval(filter_var($data["value"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
        $totalValueForeign = array_key_exists("value_foreign", $data) ? floatval(filter_var($data["value_foreign"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;

        $balances = [];
        $sum_paid = 0;
        $sum_spend = 0;
        foreach ($data["balance"] as $user_id => $bdata) {
            $user = intval(filter_var($user_id, FILTER_SANITIZE_NUMBER_INT));

            if (in_array($user, $group_users)) {
                $spend = array_key_exists("spend", $bdata) ? floatval(filter_var($bdata["spend"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                $paid = array_key_exists("paid", $bdata) ? floatval(filter_var($bdata["paid"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                $paymethod = array_key_exists("paymethod", $bdata) && !empty($bdata["paymethod"]) ? intval(filter_var($bdata["paymethod"], FILTER_SANITIZE_NUMBER_INT)) : null;

                $sum_paid += $paid;
                $sum_spend += $spend;

                $spend_foreign = array_key_exists("spend_foreign", $bdata) ? floatval(filter_var($bdata["spend_foreign"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;
                $paid_foreign = array_key_exists("paid_foreign", $bdata) ? floatval(filter_var($bdata["paid_foreign"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;

                // add entry
                $balances[] = ["user" => $user, "spend" => $spend, "paid" => $paid, "paymethod" => $paymethod, "spend_foreign" => $spend_foreign, "paid_foreign" => $paid_foreign];
            }
        }
        return array($balances, $sum_paid, $sum_spend, $totalValue, $totalValueForeign);
    }

    private function addBalancesForUsers($bill, $group, $balances, $totalValue, $users) {
        foreach ($balances as $b) {
            $this->mapper->addOrUpdateBalance($bill->id, $b["user"], $b["paid"], $b["spend"], $b["paymethod"], $b["paid_foreign"], $b["spend_foreign"]);

            $userObj = $users[$b["user"]];

            if ($group->add_finances > 0 && $bill->settleup != 1 && $userObj->module_finance == 1) {
                if ($b["spend"] > 0) {
                    $entry = new FinancesEntry([
                        "date" => $bill->date,
                        "time" => $bill->time,
                        "description" => $bill->name,
                        "type" => 0,
                        "value" => $b["spend"],
                        "user" => $b["user"],
                        "common" => 1,
                        "common_value" => $totalValue,
                        "bill" => $bill->id,
                        "lng" => $bill->lng,
                        "lat" => $bill->lat,
                        "acc" => $bill->acc,
                        "paymethod" => $b["paymethod"]
                    ]);

                    $entry->category = $this->finance_service->getDefaultOrAssignedCategory($b["user"], $entry);
                    $this->finance_service->addOrUpdateFromBill($entry);
                } else {
                    $this->finance_service->deleteEntrywithBill($bill->id, $b["user"]);
                }
            }
        }
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

}
