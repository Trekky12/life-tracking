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

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        PlanMapper $mapper,
        ExerciseMapper $exercise_mapper,
        BodypartMapper $bodypart_mapper,
        MuscleMapper $muscle_mapper,
        Translator $translation,
        SettingsMapper $settings_mapper
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;

        $this->exercise_mapper = $exercise_mapper;
        $this->bodypart_mapper = $bodypart_mapper;
        $this->muscle_mapper = $muscle_mapper;

        $this->translation = $translation;
        $this->settings_mapper = $settings_mapper;
    }

    public function index($is_template = false, $archive = 0) {
        $plans = $this->mapper->getAllPlans('id', $is_template, $archive);

        return new Payload(Payload::$RESULT_HTML, [
            'plans' => $plans,
            'archive' => $archive
        ]);
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

    public function getPlanExercises($plan_id, $session_exercises = [], $show_planned = false) {

        $exercises = $this->exercise_mapper->getAll();
        $bodyparts = $this->bodypart_mapper->getAll();
        $muscles = $this->muscle_mapper->getAll();

        $plan_exercises = $this->mapper->getExercises($plan_id);
        $selected_exercises = $session_exercises;

        if ($show_planned) {
            $session_exercises_ids = array_map(function ($exercise) {
                return $exercise["plans_exercises_id"];
            }, $session_exercises);

            // Check session day and filter to plan session of the saved day
            $session_day = null;
            $day_start = true;
            foreach ($session_exercises as $se) {
                if ($se["type"] == "day") {
                    $session_day = $se;
                    $day_start = false;
                    break;
                }
            }
            foreach ($plan_exercises as $pe) {
                if (!is_null($session_day) && $pe["type"] == "day") {
                    if ($pe["id"] == $se["plans_exercises_id"]) {
                        $day_start = true;
                    } else {
                        $day_start = false;
                    }
                }

                if ($day_start && !in_array($pe['id'], $session_exercises_ids)) {
                    $pe['is_finished'] = 0;
                    $selected_exercises[] = $pe;
                }
            }
        } else {
            if (empty($session_exercises)) {
                $selected_exercises = $plan_exercises;
            }
        }

        $exercise_ids = array_map(function ($exercise) {
            return $exercise["exercise"];
        }, $selected_exercises);

        $exercise_muscles = $this->exercise_mapper->getMusclesOfExercisesFull($exercise_ids);

        $all_selected_muscles = ["primary" => [], "secondary" => []];
        $exercises_print = [];
        $exercise_idx = 0;
        foreach ($selected_exercises as $idx => $se) {
            $exercise = !is_null($se["exercise"]) ? $exercises[$se["exercise"]] : null;

            if (!is_null($se["exercise"])) {
                $set_description = array_map(function ($set) use ($exercise) {
                    $description = [];
                    if ($exercise->isCategoryReps() || $exercise->isCategoryRepsWeight()) {
                        $description[] = sprintf("%s %s", $set["repeats"] ? $set["repeats"] : 0, $this->translation->getTranslatedString("WORKOUTS_REPEATS"));
                    }
                    if ($exercise->isCategoryRepsWeight()) {
                        $description[] = sprintf("%s %s", $set["weight"] ? $set["weight"] : 0, $this->translation->getTranslatedString("WORKOUTS_KG"));
                    }
                    if ($exercise->isCategoryTime() || $exercise->isCategoryDistanceTime()) {
                        if (array_key_exists("time_type", $set) && $set["time_type"] == "min") {
                            $description[] = sprintf("%s %s", $set["time"] ? $set["time"] : 0, $this->translation->getTranslatedString("WORKOUTS_MINUTES"));
                        } else {
                            $description[] = sprintf("%s %s", $set["time"] ? $set["time"] : 0, $this->translation->getTranslatedString("WORKOUTS_SECONDS"));
                        }
                    }
                    if ($exercise->isCategoryDistanceTime()) {
                        $description[] = sprintf("%s %s", $set["distance"] ? $set["distance"] : 0, $this->translation->getTranslatedString("WORKOUTS_KM"));
                    }
                    return implode(', ', $description);
                }, $se["sets"]);
            }

            // get muscles
            $primary = [];
            $secondary = [];
            if (!is_null($exercise) && array_key_exists($exercise->id, $exercise_muscles)) {
                foreach ($exercise_muscles[$exercise->id] as $em) {
                    if ($em["is_primary"] > 0) {
                        $primary[] = $muscles[$em["muscle"]];
                    } else {
                        $secondary[] = $muscles[$em["muscle"]];
                    }
                }
            }

            $all_selected_muscles["primary"] = array_merge($all_selected_muscles["primary"], $primary);
            $all_selected_muscles["secondary"] = array_merge($all_selected_muscles["secondary"], $secondary);

            $exercise_print = [
                "exercise" => $exercise,
                "mainBodyPart" => !is_null($exercise) && array_key_exists($exercise->mainBodyPart, $bodyparts) ? $bodyparts[$exercise->mainBodyPart]->name : '',
                "mainMuscle" => !is_null($exercise) && array_key_exists($exercise->mainMuscle, $muscles) ? $muscles[$exercise->mainMuscle]->name : '',
                "primary_muscles" => $primary,
                "secondary_muscles" => $secondary,
                "id" => array_key_exists("id", $se) ? $se["id"] : null,
                "sets" => $se["sets"],
                "idx" => $exercise_idx,
                "type" => $se["type"],
                "notice" => $se["notice"],
                "is_child" => $se["is_child"],
                "children" => [],
                "set_description" => !is_null($exercise) ? $set_description : null,
                "plans_exercises_id" => $se["plans_exercises_id"],
                "is_finished" => is_null($session_exercises) || empty($session_exercises) ? 0 : $se["is_finished"],
            ];

            // add as child
            if ($se["type"] === "exercise" && $se["is_child"] > 0) {
                $exercises_print[count($exercises_print) - 1]["children"][] = $exercise_print;
            } else {
                $exercises_print[] = $exercise_print;
            }
            $exercise_idx++;
        }

        return [$exercises_print, $all_selected_muscles];
    }

    public function getWorkoutDays($plan_id) {
        return $this->mapper->getWorkoutDays($plan_id);
    }
}
