<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\MainService;
use App\Application\Responder\JSONResultResponder;

class CronAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, MainService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->cron();
        return $this->responder->respond($payload);
    }

}
