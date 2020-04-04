<?php

namespace App\Application\Action\Finances\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesStatsService;
use App\Application\Responder\HTMLTemplateResponder;

class FinancesYearAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, FinancesStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $year = $request->getAttribute('year');

        $stats = $this->service->statsYear($year);

        return $this->responder->respond($stats->withTemplate('finances/stats/year.twig'));
    }

}
