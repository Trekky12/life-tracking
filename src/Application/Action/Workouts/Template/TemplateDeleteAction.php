<?php

namespace App\Application\Action\Workouts\Template;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Plan\PlanRemover;
use App\Application\Responder\DeleteResponder;

class TemplateDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, PlanRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
