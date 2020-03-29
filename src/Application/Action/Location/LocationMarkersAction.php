<?php

namespace App\Application\Action\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\LocationService;
use App\Application\Responder\JSONResponder;
use App\Domain\Main\Utility\DateUtility;

class LocationMarkersAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, LocationService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData);

        $markers = $this->service->getMarkers($from, $to);

        return $this->responder->respond($markers);
    }

}
