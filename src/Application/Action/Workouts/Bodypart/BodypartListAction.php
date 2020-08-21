<?php

namespace App\Application\Action\Workouts\Bodypart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Bodypart\BodypartService;
use App\Application\Responder\HTMLTemplateResponder;

class BodypartListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, BodypartService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('workouts/bodypart/index.twig'));
    }

}
