<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\TripEventService;
use App\Application\Responder\JSONResultResponder;
use App\Domain\Main\Utility\DateUtility;

class EventMarkersAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, TripEventService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('trip');
        
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData, null, null);

        $markers = $this->service->getMarkers($hash, $from, $to);

        return $this->responder->respond($markers);
    }

}
