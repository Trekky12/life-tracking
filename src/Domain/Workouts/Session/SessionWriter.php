<?php

namespace App\Domain\Workouts\Session;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Workouts\Plan\PlanService;
use App\Domain\Workouts\Plan\PlanMapper;

class SessionWriter extends ObjectActivityWriter {

    private $plan_service;
    private $plan_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, SessionMapper $mapper, PlanService $plan_service, PlanMapper $plan_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->plan_service = $plan_service;
        $this->plan_mapper = $plan_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $plan = $this->plan_service->getFromHash($additionalData["plan"]);

        if (!$this->plan_service->isMember($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['plan'] = $plan->id;

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        /**
         * Save exercises
         */
        $this->mapper->deleteExercises($id);
        if (array_key_exists("exercises", $data) && is_array($data["exercises"])) {
            $exercises = [];
            foreach ($data["exercises"] as $idx => $exercise) {

                $exercise_id = array_key_exists("id", $exercise) && !empty($exercise["id"]) ? intval(filter_var($exercise["id"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $type = array_key_exists("type", $exercise) && !empty($exercise["type"]) ? filter_var($exercise["type"], FILTER_SANITIZE_STRING) : 'exercise';
                $notice = array_key_exists("notice", $exercise) && !empty($exercise["notice"]) ? filter_var($exercise["notice"], FILTER_SANITIZE_STRING) : null;
                $is_child = array_key_exists("is_child", $exercise) && !empty($exercise["is_child"]) ? intval(filter_var($exercise["is_child"], FILTER_SANITIZE_NUMBER_INT)) : 0;           

                $sets = [];
                if (array_key_exists("sets", $exercise) && is_array($exercise["sets"])) {
                    foreach ($exercise["sets"] as $set) {
                        $repeats = array_key_exists("repeats", $set) && !empty($set["repeats"]) ? intval(filter_var($set["repeats"], FILTER_SANITIZE_NUMBER_INT)) : null;
                        $weight = array_key_exists("weight", $set) && !empty($set["weight"]) ? floatval(filter_var($set["weight"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;
                        $time = array_key_exists("time", $set) && !empty($set["time"]) ? floatval(filter_var($set["time"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;
                        $distance = array_key_exists("distance", $set) && !empty($set["distance"]) ? floatval(filter_var($set["distance"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;

                        $sets[] = ["repeats" => $repeats, "weight" => $weight, "time" => $time, "distance" => $distance];
                    }
                }

                $exercises[] = ["id" => $exercise_id, "position" => $idx, "sets" => $sets, "type" => $type, "notice" => $notice, "is_child" => $is_child > 0 ? 1 : 0];
            }
            
            $this->mapper->addExercises($entry->id, $exercises);
        }

        return $payload;
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
