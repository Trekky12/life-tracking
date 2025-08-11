<?php

namespace App\Domain\Splitbill;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\Main\Translator;

abstract class BaseBillWriter extends ObjectActivityWriter {

    protected $group_service;
    protected $group_mapper;
    protected $translation;
    protected $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, SplitbillGroupService $group_service, GroupMapper $group_mapper, Translator $translation) {
        parent::__construct($logger, $user, $activity);
        $this->group_service = $group_service;
        $this->group_mapper = $group_mapper;
        $this->translation = $translation;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $group = $this->group_service->getFromHash($additionalData["group"]);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!is_null($id) && !$this->service->isChildOf($group->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        // Members can only update their paymethod
        if ($this->getService()->isOwner($id) === false) {

            $splitbill_groups_users = $this->group_service->getUsers($group->id);
            list($balances, $sum_paid, $sum_spend, $totalValue, $totalValueForeign) = $this->filterBalances($data, $splitbill_groups_users);

            $user_id = $this->current_user->getUser()->id;
            if (array_key_exists($user_id, $balances)) {

                $balance = $balances[$user_id];
                $update = $this->getMapper()->updatePaymethod($id, $balance["user"], $balance["paymethod"], $balance["account_to"]);

                return new Payload(Payload::$STATUS_UPDATE_PAYMETHOD, $balance);
            }
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['sbgroup'] = $group->id;

        $payload = parent::save($id, $data, $additionalData);
        $bill = $payload->getResult();

        /**
         * Save Balance
         */
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

        return $payload;
    }

    protected function addBalances($bill, $sbgroup, $splitbill_groups_users, $data) {

        $existing_balance = $this->getMapper()->getBalance($bill->id);

        $removed_users = array_diff(array_keys($existing_balance), array_keys($splitbill_groups_users));

        list($balances, $sum_paid, $sum_spend, $totalValue, $totalValueForeign) = $this->filterBalances($data, $splitbill_groups_users);

        // floating point comparison
        if (!empty($balances) && $totalValue > 0 && (abs(($totalValue - $sum_paid) / $totalValue) < 0.00001) && (abs(($totalValue - $sum_spend) / $totalValue) < 0.00001)) {
            $this->logger->info('Add balance for bill', array("bill" => $bill->id, "balances" => $balances));

            foreach ($balances as $balance) {
                $this->getMapper()->addOrUpdateBalance($bill->id, $balance["user"], $balance["paid"], $balance["spend"], $balance["paymethod"], $balance["account_to"], $balance["paid_foreign"], $balance["spend_foreign"]);
            }

            // delete entries for users removed from the group
            foreach ($removed_users as $ru) {
                $this->getMapper()->deleteBalanceofUser($bill->id, $ru);
            }
        } else if ($totalValue > 0) {
            $this->logger->error('Balance for bill wrong', array("bill" => $bill, "data" => $data));

            // there was an error with the balance, so delete the bill
            $has_balance = count($existing_balance) > 0;
            // delete the bill only when there are no existing balance entries (new bill)
            if (!$has_balance) {
                $this->logger->warning('delete bill', array("bill" => $bill, "data" => $data));
                $this->getMapper()->delete($bill->id);
            }

            return false;
        }

        return true;
    }

    protected function filterBalances($data, $group_users) {
        $totalValue = array_key_exists("value", $data) ? floatval(filter_var($data["value"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
        $totalValueForeign = array_key_exists("value_foreign", $data) ? floatval(filter_var($data["value_foreign"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;

        $balances = [];
        $sum_paid = 0;
        $sum_spend = 0;
        foreach ($data["balance"] as $user_id => $bdata) {
            $user = intval(filter_var($user_id, FILTER_SANITIZE_NUMBER_INT));

            if (array_key_exists($user, $group_users)) {
                $spend = array_key_exists("spend", $bdata) ? floatval(filter_var($bdata["spend"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                $paid = array_key_exists("paid", $bdata) ? floatval(filter_var($bdata["paid"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : 0;
                $paymethod = array_key_exists("paymethod", $bdata) && !empty($bdata["paymethod"]) ? intval(filter_var($bdata["paymethod"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $account_to = array_key_exists("account_to", $bdata) && !empty($bdata["account_to"]) ? intval(filter_var($bdata["account_to"], FILTER_SANITIZE_NUMBER_INT)) : null;

                $sum_paid += $paid;
                $sum_spend += $spend;

                $spend_foreign = array_key_exists("spend_foreign", $bdata) ? floatval(filter_var($bdata["spend_foreign"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;
                $paid_foreign = array_key_exists("paid_foreign", $bdata) ? floatval(filter_var($bdata["paid_foreign"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;

                // add entry
                $balances[$user] = ["user" => $user, "spend" => $spend, "paid" => $paid, "paymethod" => $paymethod, "account_to" => $account_to, "spend_foreign" => $spend_foreign, "paid_foreign" => $paid_foreign];
            }
        }
        return array($balances, $sum_paid, $sum_spend, $totalValue, $totalValueForeign);
    }

    protected function getService() {
        return $this->service;
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
