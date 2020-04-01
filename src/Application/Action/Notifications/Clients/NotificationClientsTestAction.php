<?php

namespace App\Application\Action\Notifications\Clients;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Clients\NotificationClientsService;
use App\Application\Responder\HTMLResponder;

class NotificationClientsTestAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, NotificationClientsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $payload = $this->service->showTest($entry_id);

        return $this->responder->respond($payload->withTemplate('notifications/clients/test.twig'));
    }

}
