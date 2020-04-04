<?php

namespace App\Application\Action\Board\Board;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Board\BoardService;
use App\Application\Responder\JSONResultResponder;

class BoardChangeViewArchivedAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, BoardService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->setArchive($data);
        return $this->responder->respond($payload);
    }

}
