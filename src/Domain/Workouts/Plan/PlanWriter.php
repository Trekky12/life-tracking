<?php

namespace App\Domain\Workouts\Plan;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\Utility;

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
        if (array_key_exists("exercises", $data) && is_array($data["exercises"])) {

            $exercises_preSave = array_map(function ($ex) {
                return $ex["id"];
            }, $this->mapper->getExercises($entry->id));

            $exercises_afterSave = [];

            $exercises_new = [];
            $exercises_update = [];
            foreach ($data["exercises"] as $idx => $exercise) {

                $plan_exercise_id = array_key_exists("id", $exercise) && !empty($exercise["id"]) ? intval(filter_var($exercise["id"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $exercise_id = array_key_exists("exercise", $exercise) && !empty($exercise["exercise"]) ? intval(filter_var($exercise["exercise"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $type = array_key_exists("type", $exercise) && !empty($exercise["type"]) ? Utility::filter_string_polyfill($exercise["type"]) : 'exercise';
                $notice = array_key_exists("notice", $exercise) && !empty($exercise["notice"]) ? Utility::filter_string_polyfill($exercise["notice"]) : null;
                $is_child = array_key_exists("is_child", $exercise) && !empty($exercise["is_child"]) ? intval(filter_var($exercise["is_child"], FILTER_SANITIZE_NUMBER_INT)) : 0;

                $sets = [];
                if (array_key_exists("sets", $exercise) && is_array($exercise["sets"])) {
                    foreach ($exercise["sets"] as $set) {
                        $repeats = array_key_exists("repeats", $set) && !empty($set["repeats"]) ? intval(filter_var($set["repeats"], FILTER_SANITIZE_NUMBER_INT)) : null;
                        $weight = array_key_exists("weight", $set) && !empty($set["weight"]) ? floatval(filter_var($set["weight"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;
                        $time = array_key_exists("time", $set) && !empty($set["time"]) ? floatval(filter_var($set["time"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;
                        $time_type = array_key_exists("time_type", $set) && !empty($set["time_type"]) ? Utility::filter_string_polyfill($set["time_type"]) : null;
                        $distance = array_key_exists("distance", $set) && !empty($set["distance"]) ? floatval(filter_var($set["distance"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)) : null;

                        if (!is_null($time) && !in_array($time_type, ["min", "sec"])) {
                            $time_type = "sec";
                        }

                        $sets[] = ["repeats" => $repeats, "weight" => $weight, "time" => $time, "time_type" => $time_type, "distance" => $distance];
                    }
                }

                $exercise = ["id" => $plan_exercise_id, "exercise" => $exercise_id, "position" => $idx, "sets" => $sets, "type" => $type, "notice" => $notice, "is_child" => $is_child > 0 ? 1 : 0];

                if (!is_null($plan_exercise_id)) {
                    $exercises_afterSave[] = $plan_exercise_id;
                    $exercises_update[] = $exercise;
                } else {
                    $exercises_new[] = $exercise;
                }
            }

            // add new exercises (id == null)
            if (!empty($exercises_new)) {
                $this->mapper->addExercises($entry->id, $exercises_new);
            }

            // update exercises
            if (!empty($exercises_update)) {
                $this->mapper->updateExercises($entry->id, $exercises_update);
            }

            // delete missing exercises
            $exercises_removed = array_diff($exercises_preSave, $exercises_afterSave);
            if (!empty($exercises_removed)) {
                $this->mapper->deleteExercises($entry->id, $exercises_removed);
            }
        } else {
            $this->mapper->deleteExercises($id);
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
