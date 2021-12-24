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
                        $time_type = array_key_exists("time_type", $set) && !empty($set["time_type"]) ? filter_var($set["time_type"], FILTER_SANITIZE_STRING) : null;
                        $distance = array_key_exists("distance", $set) && !empty($set["distance"]) ? floatval(filter_var($set["distance"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;

                        if(!is_null($time) && !in_array($time_type, ["min", "sec"])){
                            $time_type = "sec";
                        }

                        $sets[] = ["repeats" => $repeats, "weight" => $weight, "time" => $time, "time_type" => $time_type, "distance" => $distance];                        
                    }
                }     
                

                $exercises[] = ["id" => $exercise_id, "position" => $idx, "sets" => $sets, "type" => $type, "notice" => $notice, "is_child" => $is_child > 0 ? 1 : 0];
            }

            $this->mapper->addExercises($entry->id, $exercises);
        }

        return $payload;
    }

    public function getObjectViewRoute(): string {
        return 'workouts';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "workouts";
    }

}
