<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\TripEventService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class EventEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TripEventService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $trip_hash = $request->getAttribute('trip');
        $entry_id = $request->getAttribute('id');
        
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData, null, null);
        
        $data = $this->service->edit($trip_hash, $entry_id, $from, $to);
        return $this->responder->respond($data->withTemplate('trips/events/edit.twig'));
    }

}
