<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Workouts\Session\SessionCreator;

class SessionSaveExerciseAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, SessionCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('plan');
        $session = $request->getAttribute('session');
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $payload = $this->service->saveExercise($hash, $session, $data);
        return $this->responder->respond($payload);
    }

}
