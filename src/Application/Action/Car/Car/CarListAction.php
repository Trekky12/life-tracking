<?php

namespace App\Application\Action\Car\Car;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\CarService;
use App\Application\Responder\HTMLTemplateResponder;

class CarListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CarService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('cars/control/index.twig'));
    }

}
