<?php

namespace App\Application\Action\Activity;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Activity\ActivityService;
use App\Application\Responder\JSONResponder;

class ActivityListAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, ActivityService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->getActivities($data);

        return $this->responder->respond($payload);
    }

}
