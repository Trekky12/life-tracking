<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Session\SessionService;
use App\Application\Responder\HTMLTemplateResponder;

class SessionEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SessionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute('plan');
        $entry_id = $request->getAttribute('id');
        
        $data = $this->service->edit($plan_hash, $entry_id);
        return $this->responder->respond($data->withTemplate('workouts/sessions/edit.twig'));
    }

}
