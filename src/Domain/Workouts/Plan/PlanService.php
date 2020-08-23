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

class PlanService extends Service {

    private $exercise_mapper;
    private $bodypart_mapper;
    private $muscle_mapper;
    private $translation;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            PlanMapper $mapper,
            ExerciseMapper $exercise_mapper,
            BodypartMapper $bodypart_mapper,
            MuscleMapper $muscle_mapper,
            Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;

        $this->exercise_mapper = $exercise_mapper;
        $this->bodypart_mapper = $bodypart_mapper;
        $this->muscle_mapper = $muscle_mapper;
        
        $this->translation = $translation;
    }

    public function index() {
        $plans = $this->mapper->getAll();

        return new Payload(Payload::$RESULT_HTML, ['plans' => $plans]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);

        $exercises = $this->exercise_mapper->getAll();
        $bodyparts = $this->bodypart_mapper->getAll();
        $muscles = $this->muscle_mapper->getAll();
        $selected_exercises = $this->mapper->getExercises($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'exercises' => $exercises,
            'bodyparts' => $bodyparts,
            'muscles' => $muscles,
            'selected_exercises' => $selected_exercises
        ]);
    }

    public function view($hash): Payload {

        $plan = $this->getFromHash($hash);

        if (!$this->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $exercises = $this->exercise_mapper->getAll();
        $bodyparts = $this->bodypart_mapper->getAll();
        $muscles = $this->muscle_mapper->getAll();
        $selected_exercises = $this->mapper->getExercises($plan->id);

        $exercises_print = [];
        foreach ($selected_exercises as $se) {
            $exercise = $exercises[$se["exercise"]];

            $sets = array_map(function($set) use ($exercise) {
                $description = [];
                if ($exercise->isCategoryReps() || $exercise->isCategoryRepsWeight()) {
                    $description[] = sprintf("%s %s", $set["repeats"], $this->translation->getTranslatedString("WORKOUTS_REPEATS"));
                }
                if ($exercise->isCategoryRepsWeight()) {
                    $description[] = sprintf("%s %s", $set["weight"], $this->translation->getTranslatedString("WORKOUTS_KG"));
                }
                if ($exercise->isCategoryTime() || $exercise->isCategoryDistanceTime()) {
                    $description[] = sprintf("%s %s", $set["time"], $this->translation->getTranslatedString("WORKOUTS_MINUTES"));
                }
                if ($exercise->isCategoryDistanceTime()) {
                    $description[] = sprintf("%s %s", $set["distance"], $this->translation->getTranslatedString("WORKOUTS_KM"));
                }
                return implode(', ', $description);
            }, $se["sets"]);

            $exercises_print[] = ["exercise" => $exercise,
                "mainBodyPart" => $bodyparts[$exercise->mainBodyPart]->name,
                "mainMuscle" => $muscles[$exercise->mainMuscle]->name,
                "sets" => $sets
            ];
        }

        return new Payload(Payload::$RESULT_HTML, [
            "plan" => $plan,
            'exercises' => $exercises_print
        ]);
    }

}
