<?php

namespace App\Application\Action\Location\Steps;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\Steps\StepsService;
use App\Application\Responder\HTMLResponder;

class StepsYearMonthAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, StepsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $stats = $this->service->getStepsOfYearMonth($year, $month);
        return $this->responder->respond($stats->withTemplate('location/steps/steps_month.twig'));
    }

}
