<?php

namespace App\Domain\Workouts\Session;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Workouts\Plan\PlanService;
use App\Domain\Workouts\Plan\PlanMapper;

class SessionRemover extends ObjectActivityRemover {

    private $service;
    private $plan_service;
    private $plan_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, SessionService $service, SessionMapper $mapper, PlanService $plan_service, PlanMapper $plan_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->plan_service = $plan_service;
        $this->plan_mapper = $plan_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $plan = $this->plan_service->getFromHash($additionalData["plan"]);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        /**
         * NOTICE
         * All queries for this item are filtered for the current user,
         * ($select_results_of_user_only = true, $insert_user = true)
         * so actually no need check for match with parent item
         */
        if (!$this->service->isChildOf($plan->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->plan_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'workouts_sessions';
    }

    public function getObjectViewRouteParams($entry): array {
        $plan = $this->getParentMapper()->get($entry->getParentID());
        return [
            "plan" => $plan->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "workouts";
    }

}
