<?php

namespace App\Application\Action\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\LocationService;
use App\Application\Responder\JSONResponder;

class LocationAddressAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, LocationService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getQueryParams();

        $payload = $this->service->getAddress($data);

        return $this->responder->respond($payload);
    }

}
