<?php

namespace App\Application\Action\Finances\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesStatsService;
use App\Application\Responder\HTMLResponder;

class FinancesYearCategoryDetailAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, FinancesStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $year = $request->getAttribute('year');
        $category = $request->getAttribute('category');
        $type = $request->getAttribute('type');

        $stats = $this->service->statsYearTypeCategory($year, $type, $category);

        return $this->responder->respond('finances/stats/year_cat_detail.twig', $stats);
    }

}
