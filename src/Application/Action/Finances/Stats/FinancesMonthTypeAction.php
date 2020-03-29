<?php

namespace App\Application\Action\Finances\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesStatsService;
use App\Application\Responder\HTMLResponder;

class FinancesMonthTypeAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, FinancesStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $type = $request->getAttribute('type');

        $stats = $this->service->statsYearMonthType($year, $month, $type);

        return $this->responder->respond($stats->withTemplate('finances/stats/month.twig'));
    }

}
