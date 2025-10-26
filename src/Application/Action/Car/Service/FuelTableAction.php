<?php

namespace App\Application\Action\Car\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarServiceService;
use App\Application\Responder\JSONResultResponder;

class FuelTableAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, CarServiceService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('car');
        $requestData = $request->getQueryParams();
        $payload = $this->service->fuelTable($hash, $requestData);
        return $this->responder->respond($payload);
    }

}
