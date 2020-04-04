<?php

namespace App\Application\Action\Board\Card;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Board\Card\CardService;
use App\Application\Responder\JSONResultResponder;

class CardMoveStackAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, CardService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->moveCard($data);
        return $this->responder->respond($payload);
    }

}
