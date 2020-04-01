<?php

namespace App\Application\Action\Notifications\Clients;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Clients\NotificationClientsService;
use App\Application\Responder\JSONResponder;

class NotificationClientsSetCategoryAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, NotificationClientsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->setCategoryOfEndpoint($data);

        return $this->responder->respond($payload);
    }

}
