<?php

namespace App\Domain\Workouts\Plan;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Domain\Workouts\Bodypart\BodypartMapper;
use App\Domain\Workouts\Muscle\MuscleMapper;
use App\Domain\Main\Translator;
use App\Domain\Settings\SettingsMapper;
use App\Domain\Main\Utility\DateUtility;

class PlanService extends Service {

    protected $exercise_mapper;
    protected $bodypart_mapper;
    protected $muscle_mapper;
    protected $translation;
    protected $settings_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            PlanMapper $mapper,
            ExerciseMapper $exercise_mapper,
            BodypartMapper $bodypart_mapper,
            MuscleMapper $muscle_mapper,
            Translator $translation,
            SettingsMapper $settings_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;

        $this->exercise_mapper = $exercise_mapper;
        $this->bodypart_mapper = $bodypart_mapper;
        $this->muscle_mapper = $muscle_mapper;

        $this->translation = $translation;
        $this->settings_mapper = $settings_mapper;
    }

    public function index($is_template = false) {
        $plans = $this->mapper->getAllPlans('id', $is_template);

        return new Payload(Payload::$RESULT_HTML, ['plans' => $plans]);
    }

    public function edit($entry_id, $is_template = false, $use_template = null) {
        $entry = null;
        if (!empty($entry_id)) {
            $filtered = !$is_template;
            $entry = $this->mapper->get($entry_id, $filtered);
        }

        $bodyparts = $this->bodypart_mapper->getAll();

        if (is_null($entry) && !$is_template && !is_null($use_template)) {
            // load exercises from template instead of real plan
            $template = $this->mapper->getFromHash($use_template);
            list($exercises, $muscles) = $this->getPlanExercises($template->id);
        } else {
            list($exercises, $muscles) = $this->getPlanExercises($entry_id);
        }


        $selected_muscles = ["primary" => [], "secondary" => []];
        foreach ($muscles["primary"] as $sm) {
            $selected_muscles["primary"][] = $sm;
        }
        foreach ($muscles["secondary"] as $sm) {
            $selected_muscles["secondary"][] = $sm;
        }

        $allMuscles = $this->muscle_mapper->getAll();

        // Get Muscle Image
        $baseMuscleImage = $this->settings_mapper->getSetting('basemuscle_image');
        $baseMuscleImageThumbnail = '';
        if ($baseMuscleImage && $baseMuscleImage->getValue()) {
            $size = "small";
            $file_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_FILENAME);
            $baseMuscleImageThumbnail = $file_wo_extension . '-' . $size . '.' . $file_extension;
        }
        
        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'bodyparts' => $bodyparts,
            'selected_exercises' => $exercises,
            'selected_muscles' => $selected_muscles,
            'muscles' => $allMuscles,
            'baseMuscleImageThumbnail' => $baseMuscleImageThumbnail,
            'categories' => Plan::getCategories(),
            'levels' => Plan::getLevels()
        ]);
    }

    public function view($hash, $is_template = false): Payload {

        $plan = $this->getFromHash($hash);

        if (!$is_template && !$this->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        list($exercises, $muscles) = $this->getPlanExercises($plan->id);

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
            'muscles' => $muscles,
            'baseMuscleImage' => $baseMuscleImage,
            'baseMuscleImageThumbnail' => $baseMuscleImageThumbnail,
            'categories' => Plan::getCategories(),
            'levels' => Plan::getLevels()
        ]);
    }

    public function getPlanExercises($plan_id, $selected_exercises = null) {

        $exercises = $this->exercise_mapper->getAll();
        $bodyparts = $this->bodypart_mapper->getAll();
        $muscles = $this->muscle_mapper->getAll();
        if (is_null($selected_exercises)) {
            $selected_exercises = $this->mapper->getExercises($plan_id);
        }

        $exercises_print = [];
        $exercise_idx = 0;
        foreach ($selected_exercises as $idx => $se) {
            $exercise = !is_null($se["exercise"]) ? $exercises[$se["exercise"]] : null;

            if (!is_null($se["exercise"])) {
                $set_description = array_map(function($set) use ($exercise) {
                    $description = [];
                    if ($exercise->isCategoryReps() || $exercise->isCategoryRepsWeight()) {
                        $description[] = sprintf("%s %s", $set["repeats"] ? $set["repeats"] : 0, $this->translation->getTranslatedString("WORKOUTS_REPEATS"));
                    }
                    if ($exercise->isCategoryRepsWeight()) {
                        $description[] = sprintf("%s %s", $set["weight"] ? $set["weight"] : 0, $this->translation->getTranslatedString("WORKOUTS_KG"));
                    }
                    if ($exercise->isCategoryTime() || $exercise->isCategoryDistanceTime()) {
                        if($set["time_type"] == "min"){
                            $description[] = sprintf("%s %s", $set["time"] ? $set["time"] : 0, $this->translation->getTranslatedString("WORKOUTS_MINUTES"));
                        }else{
                            $description[] = sprintf("%s %s", $set["time"] ? $set["time"] : 0, $this->translation->getTranslatedString("WORKOUTS_SECONDS"));
                        }
                    }
                    if ($exercise->isCategoryDistanceTime()) {
                        $description[] = sprintf("%s %s", $set["distance"] ? $set["distance"] : 0, $this->translation->getTranslatedString("WORKOUTS_KM"));
                    }
                    return implode(', ', $description);
                }, $se["sets"]);
            }

            $exercise_print = [
                "exercise" => $exercise,
                "mainBodyPart" => !is_null($exercise) && array_key_exists($exercise->mainBodyPart, $bodyparts) ? $bodyparts[$exercise->mainBodyPart]->name : '',
                "mainMuscle" => !is_null($exercise) && array_key_exists($exercise->mainMuscle, $muscles) ? $muscles[$exercise->mainMuscle]->name : '',
                "sets" => $se["sets"],
                "idx" => $exercise_idx,
                "type" => $se["type"],
                "notice" => $se["notice"],
                "is_child" => $se["is_child"],
                "children" => [],
                "set_description" => !is_null($exercise) ? $set_description : null,
                "id" => $idx
            ];

            // add as child
            if ($se["type"] === "exercise" && $se["is_child"] > 0) {
                $exercises_print[count($exercises_print) - 1]["children"][] = $exercise_print;
            } else {
                $exercises_print[] = $exercise_print;
            }
            $exercise_idx++;
        }

        $exercise_ids = array_map(function($exercise) {
            return $exercise["exercise"];
        }, $selected_exercises);

        $exercise_muscles = $this->exercise_mapper->getMusclesOfExercises($exercise_ids);

        $selected_muscles = ["primary" => [], "secondary" => []];
        foreach ($exercise_muscles as $em) {
            if ($em["is_primary"] > 0) {
                $selected_muscles["primary"][] = $muscles[$em["muscle"]];
            } else {
                $selected_muscles["secondary"][] = $muscles[$em["muscle"]];
            }
        }
        return [$exercises_print, $selected_muscles];
    }
    
    public function getWorkoutDays($plan_id){
        return $this->mapper->getWorkoutDays($plan_id);
    }

}
