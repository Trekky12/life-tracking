<?php

namespace App\Application\Action\Notifications\Clients;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Clients\NotificationClientsService;
use App\Application\Responder\JSONResultResponder;

class NotificationClientsDeleteAPIAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, NotificationClientsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->delete($data);

        return $this->responder->respond($payload);
    }

}
