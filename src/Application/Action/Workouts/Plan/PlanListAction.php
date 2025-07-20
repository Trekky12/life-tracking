<?php

namespace App\Application\Action\Workouts\Plan;

use Slim\Http\ServerRequest as Request;
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
        $archive = $request->getParam('archive', 0);
        $index = $this->service->index(false, $archive);
        return $this->responder->respond($index->withTemplate('workouts/plan/index.twig'));
    }

}
