<?php

namespace App\Application\Action\Board\Stack;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Board\Stack\StackService;
use App\Application\Responder\JSONResultResponder;

class StackDataAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, StackService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $payload = $this->service->getData($entry_id);
        return $this->responder->respond($payload);
    }

}
