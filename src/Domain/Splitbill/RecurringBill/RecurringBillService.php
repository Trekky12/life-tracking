<?php

namespace App\Domain\Splitbill\RecurringBill;

use App\Domain\Splitbill\BaseBillService;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Payload\Payload;

class RecurringBillService extends BaseBillService {

    private $settings;
    private $user_service;
    private $router;
    private $group_service;
    private $paymethod_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, RecurringBillMapper $mapper, Settings $settings, UserService $user_service, RouteParser $router, SplitbillGroupService $group_service, PaymethodService $paymethod_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
        $this->user_service = $user_service;
        $this->router = $router;
        $this->group_service = $group_service;
        $this->paymethod_service = $paymethod_service;
    }

    public function index($hash): Payload {

        $group = $this->group_service->getFromHash($hash);

        if (!$this->group_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $group_users = $this->group_service->getUsers($group->id);
        $list = $this->mapper->getRecurringBills($group->id);

        $data = [
            "bills" => $list,
            "group" => $group,
            "currency" => $this->settings->getAppSettings()['i18n']['currency'],
            'units' => RecurringBill::getUnits(),
            "group_users" => $group_users
        ];

        return new Payload(Payload::$RESULT_HTML, $data);
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
            'totalValueForeign' => $totalValueForeign,
            'isSettleUp' => $isSettleUp,
            'paid_by' => $paid_by,
            'spend_by' => $spend_by,
            'settleUpPrefill' => $settleUpPrefill,
            'me' => $this->current_user->getUser()->id,
            'isOwner' => !is_null($entry_id) ? $this->isOwner($entry_id): true,
            'units' => RecurringBill::getUnits()
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getBillBalance($entry_id) {
        $balance = $this->mapper->getBalance($entry_id);

        $totalValue = $this->mapper->getBillSpend($entry_id);
        $totalValueForeign = $this->mapper->getBillSpend($entry_id, "spend_foreign");

        return [$balance, $totalValue, $totalValueForeign];
    }
}
