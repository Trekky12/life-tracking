<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\TripEventService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class EventViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TripEventService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('trip');
        
        $data = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($data, null, null);
        
        $index = $this->service->view($hash, $from, $to);

        return $this->responder->respond($index->withTemplate('trips/events/index.twig'));
    }

}
