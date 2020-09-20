<?php

namespace App\Domain\Workouts\Session;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Workouts\Plan\PlanService;
use App\Application\Payload\Payload;

class SessionService extends Service {

    private $plan_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, SessionMapper $mapper, PlanService $plan_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->plan_service = $plan_service;
    }

    public function view($hash): Payload {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        
        $sessions = $this->mapper->getAll();

        $response_data = [
            'plan' => $plan,
            'sessions' => $sessions
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function edit($hash, $entry_id) {

        $plan = $this->plan_service->getFromHash($hash);

        if (!$this->plan_service->isOwner($plan->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        
        $selected_exercises = $this->mapper->getExercises($entry_id);
        list($exercises, $muscles) = $this->plan_service->getPlanExercises($plan->id, !empty($selected_exercises)? $selected_exercises : null);

        $response_data = [
            'entry' => $entry,
            'plan' => $plan,
            'exercises' => $exercises
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

}
