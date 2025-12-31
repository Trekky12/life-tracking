<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Session\SessionCreator;
use App\Application\Responder\RedirectResponder;

class SessionCreateAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, SessionCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute('plan');
        $payload = $this->service->create($plan_hash);

        $entry = $payload->getResult();

        return $this->responder->respond('workouts_sessions_continue', 301, true, ["plan" => $plan_hash, "session" => $entry->id]);
    }
}
