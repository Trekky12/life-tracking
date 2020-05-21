<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Splitbill\Bill\BillMapper;
use App\Domain\Splitbill\Group\SplitbillGroupService;

class SplittedBillsBalanceWidget {

    private $logger;
    private $current_user;
    private $group_service;
    private $bill_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, SplitbillGroupService $group_service, BillMapper $bill_mapper) {
        $this->logger = $logger;
        $this->current_user = $user;
        $this->group_service = $group_service;
        $this->bill_mapper = $bill_mapper;
    }

    public function getContent() {
        $user_groups = $this->group_service->getUserGroups();

        $groups = $this->group_service->getGroups();

        $balances = $this->bill_mapper->getBalances();
        $result = [];

        foreach ($user_groups as $group_id) {
            $group = $groups[$group_id];
            $balance = array_key_exists($group_id, $balances) ? $balances[$group_id] : null;

            $result[$group_id] = ["name" => $group->name, "balance" => $balance];
        }

        return $result;
    }

}
