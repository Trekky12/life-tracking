<?php

namespace App\Application\Action\Finances\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesStatsService;
use App\Application\Responder\HTMLResponder;

class FinancesBudgetAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, FinancesStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $budget = $request->getAttribute('budget');

        $data = $this->service->budget($budget);

        return $this->responder->respond('finances/stats/budget.twig', $data);
    }

}
