<?php

namespace App\Domain\Workouts\Plan;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Domain\Workouts\Bodypart\BodypartMapper;
use App\Domain\Workouts\Muscle\MuscleMapper;

class PlanService extends Service {

    private $exercise_mapper;
    private $bodypart_mapper;
    private $muscle_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, PlanMapper $mapper, ExerciseMapper $exercise_mapper, BodypartMapper $bodypart_mapper, MuscleMapper $muscle_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;

        $this->exercise_mapper = $exercise_mapper;
        $this->bodypart_mapper = $bodypart_mapper;
        $this->muscle_mapper = $muscle_mapper;
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

        return new Payload(Payload::$RESULT_HTML, [
            "plan" => $plan,
            'exercises' => $exercises,
            'bodyparts' => $bodyparts,
            'muscles' => $muscles,
            'selected_exercises' => $selected_exercises
        ]);
    }

}
