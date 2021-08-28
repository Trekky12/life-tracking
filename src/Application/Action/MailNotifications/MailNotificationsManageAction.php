<?php

namespace App\Application\Action\MailNotifications;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\MailNotifications\MailNotificationsService;
use App\Application\Responder\HTMLTemplateResponder;

class MailNotificationsManageAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MailNotificationsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->manage();

        return $this->responder->respond($payload->withTemplate('mail_notifications/manage.twig'));
    }

}
