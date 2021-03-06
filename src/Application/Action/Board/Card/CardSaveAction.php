<?php

namespace App\Application\Action\Board\Card;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Board\Card\CardWriter;

class CardSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, CardWriter $service) {
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
