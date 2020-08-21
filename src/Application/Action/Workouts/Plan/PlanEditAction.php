<?php

namespace App\Application\Action\Workouts\Plan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Plan\PlanService;
use App\Application\Responder\HTMLTemplateResponder;

class PlanEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, PlanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('workouts/plan/edit.twig'));
    }

}
