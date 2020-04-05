<?php

namespace App\Domain\Splitbill\Group;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class GroupWriter extends ObjectActivityWriter {

    private $group_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, GroupMapper $mapper, SplitbillGroupService $group_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->group_service = $group_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        if ($this->group_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        return $payload;
    }

    public function getObjectViewRoute(): string {
        return 'splitbill_groups_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "splitbills";
    }

}
