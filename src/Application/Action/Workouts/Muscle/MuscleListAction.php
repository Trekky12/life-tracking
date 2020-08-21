<?php

namespace App\Application\Action\Workouts\Muscle;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Muscle\MuscleService;
use App\Application\Responder\HTMLTemplateResponder;

class MuscleListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MuscleService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('workouts/muscle/index.twig'));
    }

}
