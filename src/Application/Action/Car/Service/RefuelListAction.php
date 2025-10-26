<?php

namespace App\Application\Action\Car\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarServiceService;
use App\Application\Responder\HTMLTemplateResponder;

class RefuelListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CarServiceService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('car');
        $index = $this->service->indexRefuel($hash);
        return $this->responder->respond($index->withTemplate('cars/refuel/index.twig'));
    }

}
