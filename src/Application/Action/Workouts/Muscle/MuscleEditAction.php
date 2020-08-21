<?php

namespace App\Application\Action\Workouts\Muscle;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Muscle\MuscleService;
use App\Application\Responder\HTMLTemplateResponder;

class MuscleEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MuscleService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('workouts/muscle/edit.twig'));
    }

}
