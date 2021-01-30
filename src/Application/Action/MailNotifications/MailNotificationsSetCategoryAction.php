<?php

namespace App\Application\Action\MailNotifications;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\MailNotifications\MailNotificationsService;
use App\Application\Responder\JSONResultResponder;

class MailNotificationsSetCategoryAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, MailNotificationsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->setCategoryForUser($data);

        return $this->responder->respond($payload);
    }

}
