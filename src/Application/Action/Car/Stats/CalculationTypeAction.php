<?php

namespace App\Application\Action\Car\Stats;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarServiceService;
use App\Application\Responder\SaveJSONResponder;

class CalculationTypeAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, CarServiceService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();

        $payload = $this->service->setCalculationType($data);
        
        return $this->responder->respond($payload);
    }

}
