<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Session\SessionCreator;
use App\Application\Responder\HTMLTemplateResponder;

class SessionContinueAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SessionCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute('plan');
        $session = $request->getAttribute('session');
        $data = $this->service->continue($plan_hash, $session);
        return $this->responder->respond($data->withTemplate('workouts/sessions/create.twig'));
    }
}
