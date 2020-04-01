<?php

namespace App\Application\Action\Notifications\Clients;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\NotificationsService;
use App\Application\Responder\NotificationClients\TestNotificationResponder;

class NotificationClientsTestSendAction {

    private $responder;
    private $service;

    public function __construct(TestNotificationResponder $responder, NotificationsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $payload = $this->service->sendTestNotification($entry_id, $data);
        return $this->responder->respond($payload);
    }

}
