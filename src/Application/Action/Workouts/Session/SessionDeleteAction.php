<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Session\SessionRemover;
use App\Application\Responder\DeleteResponder;

class SessionDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, SessionRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute("plan");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["plan" => $plan_hash]);
        return $this->responder->respond($payload);
    }

}
