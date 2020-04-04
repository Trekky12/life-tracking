<?php

namespace App\Application\Action\Finances\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesStatsService;
use App\Application\Responder\HTMLTemplateResponder;

class FinancesMonthTypeCategoryAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, FinancesStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $category = $request->getAttribute('category');
        $type = $request->getAttribute('type');

        $stats = $this->service->statsYearMonthTypeCategory($year, $month, $type, $category);

        return $this->responder->respond($stats->withTemplate('finances/stats/month_cat.twig'));
    }

}
