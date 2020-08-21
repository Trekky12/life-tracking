<?php

namespace App\Domain\Workouts\Plan;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class PlanWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, PlanMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {
        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        return $payload;
    }

    public function getObjectViewRoute(): string {
        return 'workouts_plans';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts";
    }

}
