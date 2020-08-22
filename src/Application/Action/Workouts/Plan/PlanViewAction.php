<?php

namespace App\Application\Action\Workouts\Plan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Plan\PlanService;
use App\Application\Responder\HTMLTemplateResponder;

class PlanViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, PlanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('plan');
        $index = $this->service->view($hash);
        return $this->responder->respond($index->withTemplate('workouts/plan/view.twig'));
    }

}
