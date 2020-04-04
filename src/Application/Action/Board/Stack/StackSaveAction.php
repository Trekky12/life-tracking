<?php

namespace App\Application\Action\Board\Stack;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Board\Stack\StackWriter;

class StackSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, StackWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $payload = $this->service->save($id, $data);
        return $this->responder->respond($payload);
    }

}
