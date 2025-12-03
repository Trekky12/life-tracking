<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Session\SessionCreator;
use App\Application\Responder\HTMLTemplateResponder;

class SessionCreateAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SessionCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute('plan');
        $data = $this->service->create($plan_hash);
        return $this->responder->respond($data->withTemplate('workouts/sessions/create.twig'));
    }
}
