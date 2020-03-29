<?php

namespace App\Application\Action\Car\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarServiceStatsService;
use App\Application\Responder\HTMLResponder;

class CarServiceStatsAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, CarServiceStatsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $stats = $this->service->stats();
        return $this->responder->respond($stats->withTemplate('cars/stats.twig'));
    }

}
