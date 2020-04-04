<?php

namespace App\Application\Action\Board\Stack;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Board\Stack\StackRemover;
use App\Application\Responder\DeleteResponder;

class StackDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, StackRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
