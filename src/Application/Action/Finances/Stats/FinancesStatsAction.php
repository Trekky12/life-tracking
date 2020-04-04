<?php

namespace App\Application\Action\Finances\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesStatsService;
use App\Application\Responder\HTMLTemplateResponder;

class FinancesStatsAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, FinancesStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $stats = $this->service->statsTotal();
        return $this->responder->respond($stats->withTemplate('finances/stats/index.twig'));
    }

}
