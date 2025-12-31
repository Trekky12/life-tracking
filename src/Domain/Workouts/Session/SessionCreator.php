<?php

namespace App\Domain\Workouts\Session;

use Psr\Log\LoggerInterface;
use App\Application\Payload\Payload;
use App\Domain\Base\CurrentUser;
use App\Domain\Service;
use App\Domain\Workouts\Plan\PlanService;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Domain\Main\Utility\Utility;

class SessionCreator extends Service {

    private $session_writer;
    private $plan_service;
    private $exercise_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        SessionMapper $mapper,
        SessionWriter $session_writer,
        PlanService $plan_service,
        ExerciseMapper $exercise_mapper
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->session_writer = $session_writer;
        $this->plan_service = $plan_service;
        $this->exercise_mapper = $exercise_mapper;
    }

    public function create($hash, $id = null) {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data = [
            "date" => date('Y-m-d'),
            "start_time" => date("H:i:s")
        ];

        $this->logger->debug('Create new workout session', array("plan" => $plan, "data" => $data));

        $entry = $this->session_writer->save(null, $data, ["plan" => $plan->getHash()]);

        return $entry;
    }

    public function continue($hash, $session) {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->isChildOf($plan->id, $session)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($session);

        $session_exercises = $this->mapper->getExercises($session);
        list($exercises, $muscles) = $this->plan_service->getPlanExercises($plan->id, $session_exercises, true);

        $exercisesList = $this->exercise_mapper->getAll('name');

        $response_data = [
            'entry' => $entry,
            'plan' => $plan,
            'exercises' => $exercises,
            'exercisesList' => $exercisesList,
            'isWorkoutCreate' => true
        ];

        // select day only when no exercises are already saved
        if (empty($session_exercises)) {
            $days = $this->plan_service->getWorkoutDays($plan->id);
            $response_data['workoutdays'] = $days;
        }

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function saveExercise($hash, $session, $data) {
        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->isChildOf($plan->id, $session)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (array_key_exists("exercises", $data) && is_array($data["exercises"])) {
            $exercises = [];
            foreach ($data["exercises"] as $idx => $exercise) {

                $exercise_id = array_key_exists("id", $exercise) && !empty($exercise["id"]) ? intval(filter_var($exercise["id"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $type = array_key_exists("type", $exercise) && !empty($exercise["type"]) ? Utility::filter_string_polyfill($exercise["type"]) : 'exercise';
                $notice = array_key_exists("notice", $exercise) && !empty($exercise["notice"]) ? Utility::filter_string_polyfill($exercise["notice"]) : null;
                $is_child = array_key_exists("is_child", $exercise) && !empty($exercise["is_child"]) ? intval(filter_var($exercise["is_child"], FILTER_SANITIZE_NUMBER_INT)) : 0;
                $plans_exercises_id = array_key_exists("plans_exercises_id", $exercise) && !empty($exercise["plans_exercises_id"]) ? intval(filter_var($exercise["plans_exercises_id"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $position = array_key_exists("position", $exercise) && !empty($exercise["position"]) ? intval(filter_var($exercise["position"], FILTER_SANITIZE_NUMBER_INT)) : 999;


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


                $exercises[] = [
                    "id" => $exercise_id,
                    "position" => $position,
                    "sets" => $sets,
                    "type" => $type,
                    "notice" => $notice,
                    "is_child" => $is_child > 0 ? 1 : 0,
                    "plans_exercises_id" => $plans_exercises_id
                ];
            }
            $this->mapper->addExercises($session, $exercises);
        }

        $payload = new Payload(Payload::$STATUS_NEW);
        return $payload;
    }
}
