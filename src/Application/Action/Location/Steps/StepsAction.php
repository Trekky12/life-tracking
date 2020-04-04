<?php

namespace App\Application\Action\Location\Steps;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\Steps\StepsService;
use App\Application\Responder\HTMLTemplateResponder;

class StepsAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, StepsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $stats = $this->service->getStepsPerYear();
        return $this->responder->respond($stats->withTemplate('location/steps/steps.twig'));
    }

}
