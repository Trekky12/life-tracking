<?php

namespace App\Application\Action\Workouts\Plan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Plan\PlanExportService;
use App\Application\Responder\Download\DownloadResponder;

class PlanExportAction {

    private $responder;
    private $service;

    public function __construct(DownloadResponder $responder, PlanExportService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute("plan");

        $payload = $this->service->export($plan_hash);
        return $this->responder->respond($payload);
    }

}
