<?php

namespace App\Domain\Workouts\Session;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Workouts\Plan\PlanService;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Application\Payload\Payload;
use App\Domain\Settings\SettingsMapper;
use App\Domain\Workouts\Plan\Plan;

class SessionService extends Service {

    private $plan_service;
    private $exercise_mapper;
    private $settings_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SessionMapper $mapper,
            PlanService $plan_service,
            ExerciseMapper $exercise_mapper,
            SettingsMapper $settings_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->plan_service = $plan_service;
        $this->exercise_mapper = $exercise_mapper;
        $this->settings_mapper = $settings_mapper;
    }

    public function index($hash): Payload {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $plan_sessions = $this->mapper->getFromPlan($plan->id, "date, start_time, end_time");

        $response_data = [
            'plan' => $plan,
            'sessions' => $plan_sessions
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function edit($hash, $entry_id) {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
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
            'workoutdays' => $days
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function view($hash, $entry_id): Payload {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
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
            'levels' => Plan::getLevels()
        ]);
    }

    public function stats($hash): Payload {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        list($session_exercises, $dates) = $this->mapper->getAllSessionExercises($plan->id);
        $exercisesList = $this->exercise_mapper->getAll('name');
        
        $exercises = [];
        
        foreach($session_exercises as $exercise_id => $exercise ){
            $exercise_data = ["repeats" => [], "weight" => [], "time" => [], "distance" => []];
            
            foreach($exercise as $session){
                foreach($session["sets"] as $set_idx => $set){
                    if(!array_key_exists($set_idx, $exercise_data["repeats"])){
                        $exercise_data["repeats"][$set_idx] = [];
                    }
                    if(!array_key_exists($set_idx, $exercise_data["weight"])){
                        $exercise_data["weight"][$set_idx] = [];
                    }
                    if(!array_key_exists($set_idx, $exercise_data["time"])){
                        $exercise_data["time"][$set_idx] = [];
                    }
                    if(!array_key_exists($set_idx, $exercise_data["distance"])){
                        $exercise_data["distance"][$set_idx] = [];
                    }
                    
                    $exercise_data["repeats"][$set_idx][] = ["x" => $session["date"], "y" => $set["repeats"]];
                    $exercise_data["weight"][$set_idx][] = ["x" => $session["date"], "y" => $set["weight"]];
                    $exercise_data["time"][$set_idx][] = ["x" => $session["date"], "y" => $set["time"]];
                    $exercise_data["distance"][$set_idx][] = ["x" => $session["date"], "y" => $set["distance"]];
                }
            }
            
            $exercises[] = ["exercise" => $exercisesList[$exercise_id], "data" => $exercise_data];
        }

        $response_data = [
            'plan' => $plan,
            'exercisesList' => $exercisesList,
            'exercises' => $exercises
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

}
