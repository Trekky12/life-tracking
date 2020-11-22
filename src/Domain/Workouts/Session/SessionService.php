<?php

namespace App\Domain\Workouts\Session;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Workouts\Plan\PlanService;
use App\Domain\Workouts\Exercise\ExerciseMapper;
use App\Application\Payload\Payload;

class SessionService extends Service {

    private $plan_service;
    private $exercise_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, SessionMapper $mapper, PlanService $plan_service, ExerciseMapper $exercise_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->plan_service = $plan_service;
        $this->exercise_mapper = $exercise_mapper;
    }

    public function view($hash): Payload {

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
        
        // load planned exercises on new entries
        $selected_exercises = null;
        if(!is_null($entry_id)){
            $selected_exercises = $this->mapper->getExercises($entry_id);
        }
        list($exercises, $muscles) = $this->plan_service->getPlanExercises($plan->id, $selected_exercises);
                
        $exercisesList = $this->exercise_mapper->getAll('name');
        
        $response_data = [
            'entry' => $entry,
            'plan' => $plan,
            'exercises' => $exercises,
            'exercisesList' => $exercisesList
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

}
