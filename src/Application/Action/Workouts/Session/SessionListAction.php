<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Session\SessionService;
use App\Application\Responder\HTMLTemplateResponder;

class SessionListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SessionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('plan');
        
        $index = $this->service->view($hash);

        return $this->responder->respond($index->withTemplate('workouts/sessions/index.twig'));
    }

}
