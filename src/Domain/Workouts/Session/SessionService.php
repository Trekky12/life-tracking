<?php

namespace App\Domain\Workouts\Session;

use App\Application\Payload\Payload;
use App\Domain\Base\CurrentUser;
use App\Domain\Service;
use App\Domain\Settings\SettingsMapper;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Domain\Workouts\Plan\Plan;
use App\Domain\Workouts\Plan\PlanService;
use Psr\Log\LoggerInterface;

class SessionService extends Service
{

    private $plan_service;
    private $exercise_mapper;
    private $settings_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        SessionMapper $mapper,
        PlanService $plan_service,
        ExerciseMapper $exercise_mapper,
        SettingsMapper $settings_mapper
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->plan_service = $plan_service;
        $this->exercise_mapper = $exercise_mapper;
        $this->settings_mapper = $settings_mapper;
    }

    public function index($hash): Payload
    {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $plan_sessions = $this->mapper->getFromPlan($plan->id, "date, start_time, end_time");

        $response_data = [
            'plan' => $plan,
            'sessions' => $plan_sessions,
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function edit($hash, $entry_id)
    {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        /**
         * NOTICE
         * All queries for this item are filtered for the current user,
         * ($select_results_of_user_only = true, $insert_user = true)
         * so actually no need check for match with parent item
         */
        if (!$this->isChildOf($plan->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        $days = $this->plan_service->getWorkoutDays($plan->id);

        // load planned exercises on new entries
        $selected_exercises = null;
        if (!is_null($entry_id)) {
            $selected_exercises = $this->mapper->getExercises($entry_id);
        }
        list($exercises, $muscles) = $this->plan_service->getPlanExercises($plan->id, $selected_exercises);

        $exercisesList = $this->exercise_mapper->getAll('name');

        $response_data = [
            'entry' => $entry,
            'plan' => $plan,
            'exercises' => $exercises,
            'exercisesList' => $exercisesList,
            'workoutdays' => $days,
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function view($hash, $entry_id): Payload
    {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        /**
         * NOTICE
         * All queries for this item are filtered for the current user,
         * ($select_results_of_user_only = true, $insert_user = true)
         * so actually no need check for match with parent item
         */
        if (!$this->isChildOf($plan->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $session = $this->getEntry($entry_id);

        $selected_exercises = $this->mapper->getExercises($entry_id);
        list($exercises, $muscles) = $this->plan_service->getPlanExercises($plan->id, $selected_exercises);

        // Get Muscle Image
        $baseMuscleImage = $this->settings_mapper->getSetting('basemuscle_image');
        $baseMuscleImageThumbnail = "";
        if ($baseMuscleImage && $baseMuscleImage->getValue()) {
            $size = "small";
            $file_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_FILENAME);
            $baseMuscleImageThumbnail = $file_wo_extension . '-' . $size . '.' . $file_extension;
        }

        return new Payload(Payload::$RESULT_HTML, [
            "plan" => $plan,
            'exercises' => $exercises,
            'session' => $session,
            'muscles' => $muscles,
            'baseMuscleImage' => $baseMuscleImage,
            'baseMuscleImageThumbnail' => $baseMuscleImageThumbnail,
            'categories' => Plan::getCategories(),
            'levels' => Plan::getLevels(),
        ]);
    }

    public function stats($hash = null): Payload
    {
        $plan_id = null;
        if (!is_null($hash)) {
            $plan = $this->plan_service->getFromHash($hash);

            if (!$this->plan_service->isOwner($plan->id)) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }
            $plan_id = $plan->id;
        }

        list($session_exercises, $exercises_max_sets) = $this->mapper->getAllSessionExercises($plan_id);
        $exercisesList = $this->exercise_mapper->getAll('name');

        $dates = array_keys($session_exercises);

        list($start, $end) = $this->mapper->getMinMaxSessionsDate($plan_id);

        $exercisesStats = [];
        /**
         * Init exercises with data
         */
        foreach ($exercises_max_sets as $exercise_id => $max_sets) {
            $exercisesStats[$exercise_id] = ["exercise" => $exercisesList[$exercise_id], "data" => ["repeats" => [], "weight" => [], "time" => [], "distance" => []]];
        }

        /**
         * Iterate over all session dates
         */
        foreach ($session_exercises as $session_date => $exercises) {
            /**
             * Get all exercises of this date and append sets to data of exercise
             */
            foreach ($exercises as $exercise_id => $exercise_sets) {

                foreach ($exercise_sets as $set_idx => $set) {

                    if (!array_key_exists($set_idx, $exercisesStats[$exercise_id]["data"]["repeats"])) {
                        $exercisesStats[$exercise_id]["data"]["repeats"][$set_idx] = [];
                    }
                    if (!array_key_exists($set_idx, $exercisesStats[$exercise_id]["data"]["weight"])) {
                        $exercisesStats[$exercise_id]["data"]["weight"][$set_idx] = [];
                    }
                    if (!array_key_exists($set_idx, $exercisesStats[$exercise_id]["data"]["time"])) {
                        $exercisesStats[$exercise_id]["data"]["time"][$set_idx] = [];
                    }
                    if (!array_key_exists($set_idx, $exercisesStats[$exercise_id]["data"]["distance"])) {
                        $exercisesStats[$exercise_id]["data"]["distance"][$set_idx] = [];
                    }

                    $exercisesStats[$exercise_id]["data"]["repeats"][$set_idx][] = ["x" => $session_date, "y" => $set["repeats"] ? $set["repeats"] : 0];
                    $exercisesStats[$exercise_id]["data"]["weight"][$set_idx][] = ["x" => $session_date, "y" => $set["weight"] ? $set["weight"] : 0];
                    $exercisesStats[$exercise_id]["data"]["time"][$set_idx][] = ["x" => $session_date, "y" => $set["time"] ? $set["time"] : 0];
                    $exercisesStats[$exercise_id]["data"]["distance"][$set_idx][] = ["x" => $session_date, "y" => $set["distance"] ? $set["distance"] : 0];
                }
            }

            /**
             * Get missing exercises on this data and
             * set empty values for missing exercises on this date for each set
             */
            $missing_exercises_this_session = array_diff(array_keys($exercises_max_sets), array_keys($exercises));
            foreach ($missing_exercises_this_session as $missing_exercise) {
                foreach (range(0, $exercises_max_sets[$missing_exercise] - 1) as $set_idx) {
                    $exercisesStats[$missing_exercise]["data"]["repeats"][$set_idx][] = ["x" => $session_date, "y" => null];
                    $exercisesStats[$missing_exercise]["data"]["weight"][$set_idx][] = ["x" => $session_date, "y" => null];
                    $exercisesStats[$missing_exercise]["data"]["time"][$set_idx][] = ["x" => $session_date, "y" => null];
                    $exercisesStats[$missing_exercise]["data"]["distance"][$set_idx][] = ["x" => $session_date, "y" => null];
                }
            }
        }

        /**
         * Remove empty diagrams (only null y-values)
         */
        foreach ($exercisesStats as &$exercise) {
            if ($this->checkSkip($exercise["data"]["repeats"])) {
                $exercise["data"]["repeats"] = null;
            }
            if ($this->checkSkip($exercise["data"]["weight"])) {
                $exercise["data"]["weight"] = null;
            }
            if ($this->checkSkip($exercise["data"]["time"])) {
                $exercise["data"]["time"] = null;
            }
            if ($this->checkSkip($exercise["data"]["distance"])) {
                $exercise["data"]["distance"] = null;
            }
        }

        $response_data = [
            'plan' => !is_null($plan_id) ? $plan : null,
            'exercisesList' => $exercisesList,
            'exercises' => $exercisesStats,
            'start' => $start,
            'end' => $end,
            'dates' => $dates,
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function checkSkip($data)
    {
        foreach ($data as $sets) {
            foreach ($sets as $set) {
                if (!is_null($set["y"])) {
                    return false;
                }
            }
        }
        return true;
    }
}
