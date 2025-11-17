<?php

namespace App\Domain\Workouts\Exercise;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ExerciseRemover extends ObjectActivityRemover {

    private $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ExerciseMapper $mapper, ExerciseService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;

        $this->service = $service;
    }

    public function delete($id, $additionalData = null): Payload {
        $this->service->deleteImage($id);
        $this->service->deleteImage($id, true);

        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'workouts_exercises_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts";
    }

}
