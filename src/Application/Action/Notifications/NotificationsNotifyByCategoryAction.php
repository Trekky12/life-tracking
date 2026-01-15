<?php

namespace App\Application\Action\Notifications;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\NotificationsService;
use App\Application\Responder\JSONResultResponder;

class NotificationsNotifyByCategoryAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, NotificationsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $params = $request->getParams();
        $payload = $this->service->notifyByCategory($params);

        return $this->responder->respond($payload);
    }

}
