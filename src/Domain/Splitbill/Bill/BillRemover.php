<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Finances\FinancesRemover;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Finances\Transaction\TransactionMapper;
use App\Domain\Finances\Transaction\TransactionRemover;

class BillRemover extends ObjectActivityRemover {

    private $service;
    private $group_service;
    private $group_mapper;
    private $bill_notification_service;
    private $finances_remover;
    private $finances_mapper;
    protected $transaction_remover;
    protected $transaction_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            BillMapper $mapper,
            SplitbillBillService $service,
            SplitbillGroupService $group_service,
            GroupMapper $group_mapper,
            BillNotificationService $bill_notification_service,
            FinancesRemover $finances_remover,
            FinancesMapper $finances_mapper,
            TransactionRemover $transaction_remover,
            TransactionMapper $transaction_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->group_service = $group_service;
        $this->group_mapper = $group_mapper;
        $this->bill_notification_service = $bill_notification_service;
        $this->finances_remover = $finances_remover;
        $this->finances_mapper = $finances_mapper;
        $this->transaction_remover = $transaction_remover;
        $this->transaction_mapper = $transaction_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $group = $this->group_service->getFromHash($additionalData["group"]);

        if (!$this->group_service->isMember($group->id) || $this->service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if(!$this->service->isChildOf($group->id, $id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $bill = $this->service->getEntry($id);

        $this->bill_notification_service->notifyUsers("delete", $bill, $group, false);

        /**
         * Delete finance and transaction entries
         */
        $me = $this->current_user->getUser();
        $this->current_user->setUser(null);
        
        $finance_entries = $this->finances_mapper->getEntriesFromBill($bill->id);
        foreach($finance_entries as $finance_entry){
            $this->finances_mapper->setUser($finance_entry->user);
            $this->finances_remover->delete($finance_entry->id, ["is_bill_based_delete" => true]);
        }

        $transaction_entries = $this->transaction_mapper->getEntriesFromBill($bill->id);
        foreach($transaction_entries as $transaction_entry){
            $this->transaction_mapper->setUser($transaction_entry->user);
            $this->transaction_remover->delete($transaction_entry->id, ["is_bill_based_delete" => true]);
        }

        $this->current_user->setUser($me);
        $this->finances_mapper->setUser($me->id);
        $this->transaction_mapper->setUser($me->id);

        return parent::delete($id, $additionalData);
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
