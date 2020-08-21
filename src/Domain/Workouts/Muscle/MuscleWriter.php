<?php

namespace App\Domain\Workouts\Muscle;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class MuscleWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, MuscleMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {
        return parent::save($id, $data, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'workouts_muscles_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts_muscles";
    }

}
