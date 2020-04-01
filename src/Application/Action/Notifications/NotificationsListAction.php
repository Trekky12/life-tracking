<?php

namespace App\Application\Action\Notifications;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\NotificationsService;
use App\Application\Responder\JSONResponder;

class NotificationsListAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, NotificationsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->getNotifications($data);

        return $this->responder->respond($payload);
    }

}
