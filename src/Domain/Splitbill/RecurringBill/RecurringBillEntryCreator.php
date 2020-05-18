<?php

namespace App\Domain\Splitbill\RecurringBill;

use Psr\Log\LoggerInterface;
use App\Domain\Splitbill\Bill\BillWriter;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class RecurringBillEntryCreator {

    private $logger;
    private $mapper;
    private $bill_writer;
    private $group_mapper;
    private $user;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            RecurringBillMapper $mapper,
            BillWriter $bill_writer,
            GroupMapper $group_mapper,
            CurrentUser $user,
            UserService $user_service) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->bill_writer = $bill_writer;
        $this->group_mapper = $group_mapper;
        $this->user = $user;
        $this->user_service = $user_service;
    }

    public function update() {

        $bills = $this->mapper->getRecurringEntries();

        if ($bills) {
            $this->logger->addDebug('Recurring Bills', $bills);

            $groups = $this->group_mapper->getAll();

            foreach ($bills as $bill) {
                $group = $groups[$bill->getParentID()];
                $this->createElement($group, $bill);
            }

            $mentry_ids = array_map(function($el) {
                return $el->id;
            }, $bills);
            $this->mapper->updateLastRun($mentry_ids);
        }

        return true;
    }

    public function createEntry($id) {
        $bill = $this->mapper->get($id);
        $group = $this->group_mapper->get($bill->getParentID());

        $entry = $this->createElement($group, $bill);

        //$this->mapper->updateLastRun($bill->id);
        
        return new Payload(Payload::$STATUS_NEW, $entry);
    }

    private function createElement($group, $bill) {
        $balances = $this->mapper->getBalance($bill->id);
        $totalValue = $this->mapper->getBillSpend($bill->id);

        // Before creating a new splitted bill the user is checked
        // for access rights, but when running with cron there is no "current user"
        // So the current user needs to be set to the
        // recurring splitted bill creator
        $user = $this->user_service->getEntry($bill->user);
        $this->user->setUser($user);

        $data = [
            'name' => $bill->name,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'settleup' => $bill->settleup,
            'exchange_rate' => $bill->exchange_rate,
            'exchange_fee' => $bill->exchange_fee,
            'notice' => $bill->notice,
            'value' => $totalValue,
            'balance' => $balances
        ];

        $this->logger->addDebug('Recurring Bills Bill Data', array("bill" => $bill->id, "data" => $data));

        $this->bill_writer->save(null, $data, ["group" => $group->getHash()]);
        
        return $bill;
    }

}
