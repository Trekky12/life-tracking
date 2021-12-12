<?php

namespace App\Domain\Splitbill\RecurringBill;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\Splitbill\Group\SplitbillGroupService;

class RecurringBillRemover extends ObjectActivityRemover {

    private $group_service;
    private $group_mapper;
    private $service;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            RecurringBillMapper $mapper,
            SplitbillGroupService $group_service,
            GroupMapper $group_mapper,
            RecurringBillService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->group_service = $group_service;
        $this->group_mapper = $group_mapper;
        $this->service = $service;
    }

    public function delete($id, $additionalData = null): Payload {
        $group = $this->group_service->getFromHash($additionalData["group"]);

        if (!$this->group_service->isMember($group->id) || $this->service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if(!$this->service->isChildOf($group->id, $id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->group_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'splitbill_bills_recurring';
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
