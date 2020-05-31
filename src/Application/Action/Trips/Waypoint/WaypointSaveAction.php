<?php

namespace App\Application\Action\Trips\Waypoint;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\Trips\SaveWaypointResponder;
use App\Domain\Trips\Event\EventWriter;

class WaypointSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveWaypointResponder $responder, EventWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $trip_hash = $request->getAttribute("trip");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();        
        $payload = $this->service->save($id, $data, ["trip" => $trip_hash]); 
        return $this->responder->respond($payload);
    }

}
