<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\Utility;

class SplitbillBillService extends Service {

    private $settings;
    private $user_service;
    private $router;
    private $group_service;
    private $paymethod_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, BillMapper $mapper, Settings $settings, UserService $user_service, RouteParser $router, SplitbillGroupService $group_service, PaymethodService $paymethod_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->user_service = $user_service;
        $this->router = $router;
        $this->group_service = $group_service;
        $this->paymethod_service = $paymethod_service;
    }

    public function view($hash): Payload {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $table = $this->getTableDataIndex($group);

        return new Payload(Payload::$RESULT_HTML, $table);
    }

    public function table($hash, $requestData): Payload {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $table = $this->getTableData($group, $requestData);

        return new Payload(Payload::$RESULT_JSON, $table);
    }

    public function edit($hash, $entry_id, $type) {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id) || $this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if(!$this->isChildOf($group->id, $entry_id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();
        $group_users = $this->group_service->getUsers($group->id);

        list($balance, $totalValue, $totalValueForeign) = $this->getBillbalance($entry_id);

        $paymethods = $this->paymethod_service->getAllfromUsers($group_users);

        list($totalBalance, $myTotalBalance) = $this->calculateBalance($group->id);

        $isSettleUp = false;
        if ($type == 'settleup' || (!is_null($entry) && $entry->settleup == 1)) {
            $isSettleUp = true;
        }
        
        $paid_by = !is_null($entry) ? $entry->paid_by : "";
        $spend_by = !is_null($entry) ? $entry->spend_by : "";
        
        // Prefill on new settle up
        $settleUpPrefill = false;
        if ($type == 'settleup' && is_null($entry) && $myTotalBalance["balance"] < 0) {
            
            $settleUpPrefill = true;

            $totalValue = -1 * $myTotalBalance["balance"];

            $me = $this->current_user->getUser()->id;

            $balance = [];
            $balance[$me]["paid"] = $totalValue;
            $balance[$me]["spend"] = 0;
            foreach ($totalBalance as $user => $tBalance) {
                $balance[$user]["paid"] = 0;
                $balance[$user]["spend"] = $tBalance["owe"];
                
                $spend_by = $user;
            }
            
            if(count($totalBalance) > 1){
                $spend_by = "individual";
            }
            
            $paid_by = $me;
        }

        $response_data = [
            'entry' => $entry,
            'group' => $group,
            'group_users' => $group_users,
            'users' => $users,
            'balance' => $balance,
            'totalValue' => $totalValue,
            'type' => $type,
            'paymethods' => $paymethods,
            'totalValueForeign' => $totalValueForeign,
            'isSettleUp' => $isSettleUp,
            'paid_by' => $paid_by,
            'spend_by' => $spend_by,
            'settleUpPrefill' => $settleUpPrefill
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function getTableDataIndex($group, $count = 20) {

        $list = $this->getMapper()->getTableData($group->id, 0, 'DESC', $count);
        $table = $this->renderTableRows($group, $list);
        $datacount = $this->getMapper()->tableCount($group->id);

        $users = $this->user_service->getAll();

        list($balance, $my_balance) = $this->calculateBalance($group->id);

        return [
            "bills" => $table,
            "group" => $group,
            "datacount" => $datacount,
            "balance" => $balance,
            "my_balance" => $my_balance,
            "hasSplitbillTable" => true,
            "currency" => $this->settings->getAppSettings()['i18n']['currency'],
            "users" => $users
        ];
    }

    private function getTableData($group, $requestData) {

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->mapper->tableCount($group->id);
        $recordsFiltered = $this->mapper->tableCount($group->id, $searchQuery);

        $data = $this->mapper->getTableData($group->id, $sortColumnIndex, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($group, $data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $rendered_data
        ];

        return $response_data;
    }

    public function getBillBalance($entry_id) {
        $balance = $this->mapper->getBalance($entry_id);

        $totalValue = $this->mapper->getBillSpend($entry_id);
        $totalValueForeign = $this->mapper->getBillSpend($entry_id, "spend_foreign");

        return [$balance, $totalValue, $totalValueForeign];
    }

    private function renderTableRows($group, array $bills) {
        $user = $this->current_user->getUser()->id;

        $rendered_data = [];
        foreach ($bills as $bill) {
            $row = [];
            $row[] = $bill->date;
            $row[] = $bill->time;
            $row[] = $bill->name;
            if ($bill->settleup == 1) {
                $row[] = $bill->spend; // received
                $row[] = null;
                $row[] = $bill->paid;
                $row[] = null;
            } else {
                $row[] = null;
                $row[] = $bill->spend;
                $row[] = $bill->paid;
                $row[] = $bill->balance;
            }

            if ($bill->user == $user) {
                $row[] = '<a href="' . $this->router->urlFor('splitbill_bills_edit', ['id' => $bill->id, 'group' => $group->getHash()]) . '">'.Utility::getFontAwesomeIcon('fas fa-edit').'</a>';
                $row[] = '<a href="#" data-url="' . $this->router->urlFor('splitbill_bills_delete', ['id' => $bill->id, 'group' => $group->getHash()]) . '" class="btn-delete">'.Utility::getFontAwesomeIcon('fas fa-trash').'</a>';
            }

            $rendered_data[] = $row;
        }
        return $rendered_data;
    }

    private function calculateBalance($group) {
        $balance = $this->mapper->getTotalBalance($group);
        $settled = $this->mapper->getSettledUpSpendings($group, 1);

        $me = intval($this->current_user->getUser()->id);

        if (!array_key_exists($me, $balance)) {
            return array($balance, null);
        }

        $my_balance = $balance[$me]["balance"];

        foreach ($balance as $user_id => &$b) {

            $b["settled"] = array_key_exists($user_id, $settled) ? $settled[$user_id] : 0;

            if ($user_id !== $me) {

                // i owe money
                if ($my_balance < 0 && $b["balance"] > 0) {

                    // another person owes the user money
                    // but my debit is now settled
                    if ($b["balance"] > abs($my_balance)) {
                        $b["owe"] = -1 * $my_balance;
                        $my_balance = 0;
                    }
                    // I'm the only one who owes this user money
                    // my debit is now lower
                    else {
                        $b["owe"] = $b["balance"];
                        $my_balance = $my_balance - $b["balance"];
                    }
                }


                // someone owes me money
                if ($my_balance > 0 && $b["balance"] < 0) {

                    // another user owes me money
                    if ($my_balance > abs($b["balance"])) {
                        $b["owe"] = $b["balance"];
                        $my_balance = $my_balance - $b["balance"];
                    }
                    // only this user owes me money
                    // my credit is settled
                    else {
                        $b["owe"] = -1 * $my_balance;
                        $my_balance = 0;
                    }
                }
            }
        }

        $filtered = array_filter($balance, function($b) use ($me) {
            return $b["user"] != $me && ($b['balance'] != 0 or $b['owe'] != 0);
        });
        /**
         * Resort Balances
         * Big credits on top, big debits on top 
         */
        uasort($filtered, function($a, $b) {
            if ($b['owe'] > 0) {
                return $b['owe'] - $a['owe'];
            }
            return $a['owe'] - $b['owe'];
        });

        $my_balance_overview = array_key_exists($me, $balance) ? $balance[$me] : null;
        return array($filtered, $my_balance_overview);
    }

}
