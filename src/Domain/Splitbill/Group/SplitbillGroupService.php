<?php

namespace App\Domain\Splitbill\Group;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Bill\BillMapper;
use App\Domain\User\UserService;

class SplitbillGroupService extends Service {

    private $bill_mapper;
    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, GroupMapper $mapper, BillMapper $bill_mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->bill_mapper = $bill_mapper;
        $this->user_service = $user_service;
    }

    public function index($archive = 0) {
        $groups = $this->mapper->getUserItems('t.createdOn DESC, name', false, null, $archive);

        $balances = $this->bill_mapper->getBalances();

        return new Payload(Payload::$RESULT_HTML, [
            'groups' => $groups,
            'balances' => $balances,
            'archive' => $archive
        ]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }
}
