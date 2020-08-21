<?php

namespace App\Domain\Workouts\Bodypart;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class BodypartWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, BodypartMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {
        return parent::save($id, $data, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'workouts_bodyparts_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts_bodyparts";
    }

}
