<?php

namespace App\Application\Action\Notifications\Clients;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Clients\NotificationClientsService;
use App\Application\Responder\HTMLTemplateResponder;

class NotificationClientsListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, NotificationClientsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->index();

        return $this->responder->respond($payload->withTemplate('notifications/clients/index.twig'));
    }

}
