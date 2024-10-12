<?php

namespace App\Application\Action\Notifications;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\NotificationsService;
use App\Application\Responder\JSONResultResponder;

class NotificationsUnreadAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, NotificationsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->getUnreadNotifications();

        return $this->responder->respond($payload);
    }

}
