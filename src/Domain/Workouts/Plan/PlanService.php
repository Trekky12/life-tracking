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

    public function index() {
        $plans = $this->mapper->getAll();

        return new Payload(Payload::$RESULT_HTML, ['plans' => $plans]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);

        $bodyparts = $this->bodypart_mapper->getAll();
        list($exercises, $muscles) = $this->getPlanExercises($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'bodyparts' => $bodyparts,
            'selected_exercises' => $exercises
        ]);
    }

    public function view($hash): Payload {

        $plan = $this->getFromHash($hash);

        if (!$this->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        list($exercises, $muscles) = $this->getPlanExercises($plan->id);

        $exercises_print = array_map(function($exercise) {

            $set_description = array_map(function($set) use ($exercise) {
                $description = [];
                if ($exercise["exercise"]->isCategoryReps() || $exercise["exercise"]->isCategoryRepsWeight()) {
                    $description[] = sprintf("%s %s", $set["repeats"], $this->translation->getTranslatedString("WORKOUTS_REPEATS"));
                }
                if ($exercise["exercise"]->isCategoryRepsWeight()) {
                    $description[] = sprintf("%s %s", $set["weight"], $this->translation->getTranslatedString("WORKOUTS_KG"));
                }
                if ($exercise["exercise"]->isCategoryTime() || $exercise["exercise"]->isCategoryDistanceTime()) {
                    $description[] = sprintf("%s %s", $set["time"], $this->translation->getTranslatedString("WORKOUTS_MINUTES"));
                }
                if ($exercise["exercise"]->isCategoryDistanceTime()) {
                    $description[] = sprintf("%s %s", $set["distance"], $this->translation->getTranslatedString("WORKOUTS_KM"));
                }
                return implode(', ', $description);
            }, $exercise["sets"]);
            $exercise["set_description"] = $set_description;

            return $exercise;
        }, $exercises);

        // Get Muscle Image
        $baseMuscleImage = $this->settings_mapper->getSetting('basemuscle_image');
        if ($baseMuscleImage && $baseMuscleImage->getValue()) {
            $size = "small";
            $file_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($baseMuscleImage->getValue(), PATHINFO_FILENAME);
            $baseMuscleImageThumbnail = $file_wo_extension . '-' . $size . '.' . $file_extension;
        }

        return new Payload(Payload::$RESULT_HTML, [
            "plan" => $plan,
            'exercises' => $exercises_print,
            'muscles' => $muscles,
            'baseMuscleImage' => $baseMuscleImage,
            'baseMuscleImageThumbnail' => $baseMuscleImageThumbnail
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
        foreach ($selected_exercises as $idx => $se) {
            $exercise = $exercises[$se["exercise"]];

            $exercises_print[] = [
                "exercise" => $exercise,
                "mainBodyPart" => array_key_exists($exercise->mainBodyPart, $bodyparts) ? $bodyparts[$exercise->mainBodyPart]->name : '',
                "mainMuscle" => array_key_exists($exercise->mainMuscle, $muscles) ? $muscles[$exercise->mainMuscle]->name : '',
                "sets" => $se["sets"],
                "id" => $idx
            ];
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

}
