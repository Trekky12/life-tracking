<?php

namespace App\Application\Action\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\LocationService;
use App\Application\Responder\JSONResultResponder;
use App\Domain\Main\Utility\DateUtility;

class LocationMarkersAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, LocationService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $requestData = $request->getQueryParams();
        $markers = $this->service->getAllMarkers($requestData);

        return $this->responder->respond($markers);
    }

}
