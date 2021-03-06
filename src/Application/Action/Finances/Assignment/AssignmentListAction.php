<?php

namespace App\Application\Action\Finances\Assignment;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Assignment\AssignmentService;
use App\Application\Responder\HTMLTemplateResponder;

class AssignmentListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, AssignmentService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('finances/assignment/index.twig'));
    }

}
