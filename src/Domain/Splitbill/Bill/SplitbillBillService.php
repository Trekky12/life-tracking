<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\Splitbill\BaseBillService;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountService;
use App\Domain\Main\Utility\Utility;

class SplitbillBillService extends BaseBillService {

    private $settings;
    private $user_service;
    private $router;
    private $group_service;
    private $paymethod_service;
    private $account_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        BillMapper $mapper,
        Settings $settings,
        UserService $user_service,
        RouteParser $router,
        SplitbillGroupService $group_service,
        PaymethodService $paymethod_service,
        AccountService $account_service
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->user_service = $user_service;
        $this->router = $router;
        $this->group_service = $group_service;
        $this->paymethod_service = $paymethod_service;
        $this->account_service = $account_service;
    }

    public function view($hash): Payload {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $group_users = $this->group_service->getUsers($group->id);
        $table = $this->getTableDataIndex($group, $group_users);

        return new Payload(Payload::$RESULT_HTML, $table);
    }

    public function table($hash, $requestData): Payload {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $group_users = $this->group_service->getUsers($group->id);
        $table = $this->getTableData($group, $group_users, $requestData);

        return new Payload(Payload::$RESULT_JSON, $table);
    }

    public function edit($hash, $entry_id, $type) {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($group->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();
        $group_users = $this->group_service->getUsers($group->id);

        list($balance, $totalValue, $totalValueForeign) = $this->getBillbalance($entry_id);

        $paymethods = $this->paymethod_service->getAllfromUsers($group_users);
        $accounts = $this->account_service->getAllfromUsers($group_users);

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

            if (count($totalBalance) > 1) {
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
            'accounts' => $accounts,
            'totalValueForeign' => $totalValueForeign,
            'isSettleUp' => $isSettleUp,
            'paid_by' => $paid_by,
            'spend_by' => $spend_by,
            'settleUpPrefill' => $settleUpPrefill,
            'me' => $this->current_user->getUser()->id,
            'isOwner' => !is_null($entry_id) ? $this->isOwner($entry_id) : true
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function getTableDataIndex($group, $group_users, $count = 20) {
        $list = $this->getMapper()->getTableData($group->id, count($group_users), 0, 'DESC', $count);
        $table = $this->renderTableRows($group, count($group_users), $list);
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
            "users" => $users,
            'group_users' => $group_users,
            'me' => $this->current_user->getUser()->id,
        ];
    }

    private function getTableData($group, $group_users, $requestData) {

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? Utility::filter_string_polyfill($requestData["searchQuery"]) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortDirection = array_key_exists("sortDirection", $requestData) ? Utility::filter_string_polyfill($requestData["sortDirection"]) : null;

        $recordsTotal = $this->mapper->tableCount($group->id);
        $recordsFiltered = $this->mapper->tableCount($group->id, $searchQuery);

        $data = $this->mapper->getTableData($group->id, count($group_users), $sortColumnIndex, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($group, count($group_users), $data);

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

    private function renderTableRows($group, $group_members, array $bills) {
        $user = $this->current_user->getUser()->id;

        $rendered_data = [];
        foreach ($bills as $bill) {
            $row = [];
            $row[] = $bill->date;
            $row[] = $bill->time;
            $row[] = $bill->name;

            if ($group_members > 1) {
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
            } else {
                $row[] = $bill->spend;
            }

            $row[] = '<a href="' . $this->router->urlFor('splitbill_bills_edit', ['id' => $bill->id, 'group' => $group->getHash()]) . '">' . Utility::getFontAwesomeIcon('fas fa-pen-to-square') . '</a>';

            if ($bill->user == $user) {
                $row[] = '<a href="#" data-url="' . $this->router->urlFor('splitbill_bills_delete', ['id' => $bill->id, 'group' => $group->getHash()]) . '" class="btn-delete">' . Utility::getFontAwesomeIcon('fas fa-trash') . '</a>';
            } else {
                $row[] = null;
            }

            $rendered_data[] = $row;
        }
        return $rendered_data;
    }
}
