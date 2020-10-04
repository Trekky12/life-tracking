<?php

namespace App\Application\Action\Workouts\Exercise;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Exercise\ExerciseService;
use App\Application\Responder\HTMLTemplateResponder;

class ExerciseListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ExerciseService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('workouts/exercise/index.twig'));
    }

}
