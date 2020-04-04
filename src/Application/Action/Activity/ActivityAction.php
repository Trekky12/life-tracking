<?php

namespace App\Application\Action\Activity;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Activity\ActivityService;
use App\Application\Responder\HTMLTemplateResponder;

class ActivityAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ActivityService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->show();

        return $this->responder->respond($payload->withTemplate('activity/list.twig'));
    }

}
