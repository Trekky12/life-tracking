<?php

namespace App\Application\Action\Workouts\Muscle;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Muscle\MuscleService;
use App\Application\Responder\HTMLTemplateResponder;

class MuscleBaseImageAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MuscleService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->editMuscleBaseImagePage();

        return $this->responder->respond($payload->withTemplate('workouts/muscle/image.twig'));
    }

}
