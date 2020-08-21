<?php

namespace App\Application\Action\Workouts\Plan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Plan\PlanService;
use App\Application\Responder\HTMLTemplateResponder;

class PlanListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, PlanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('workouts/plan/index.twig'));
    }

}
