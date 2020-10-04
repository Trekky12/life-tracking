<?php

namespace App\Application\Action\Workouts\Exercise;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Exercise\ExerciseService;
use App\Application\Responder\Workouts\ExercisesListResponder;

class ExercisesListAction {

    private $responder;
    private $service;

    public function __construct(ExercisesListResponder $responder, ExerciseService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getQueryParams();
        $payload = $this->service->getExercises($data);

        if (array_key_exists("full", $data)) {
            return $this->responder->respond($payload->withTemplate('workouts/exercise/exercises-list.twig'));
        }

        return $this->responder->respond($payload->withTemplate('workouts/plan/exercises-list.twig'));
    }

}
