<?php

namespace App\Application\Action\Board\Board;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Board\BoardService;
use App\Application\Responder\HTMLTemplateResponder;

class BoardListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, BoardService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('boards/index.twig'));
    }

}
