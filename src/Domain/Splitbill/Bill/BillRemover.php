<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\Splitbill\Group\SplitbillGroupService;

class BillRemover extends ObjectActivityRemover {

    private $service;
    private $group_service;
    private $group_mapper;
    private $bill_notification_service;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            BillMapper $mapper,
            SplitbillBillService $service,
            SplitbillGroupService $group_service,
            GroupMapper $group_mapper,
            BillNotificationService $bill_notification_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->group_service = $group_service;
        $this->group_mapper = $group_mapper;
        $this->bill_notification_service = $bill_notification_service;
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
