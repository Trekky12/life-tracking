<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Trips\Event\EventWriter;

class EventSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, EventWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $trip_hash = $request->getAttribute("trip");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["trip" => $trip_hash]);
        return $this->responder->respond($entry->withRouteName('trips_view')->withRouteParams(["trip" => $trip_hash]));
    }

}
