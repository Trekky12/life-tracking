<?php

namespace App\Application\Action\Trips\Waypoint;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\EventRemover;
use App\Application\Responder\DeleteJSONResponder;

class WaypointDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteJSONResponder $responder, EventRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $trip_hash = $request->getAttribute("trip");
        $requestData = $request->getQueryParams();
        $id = array_key_exists("id", $requestData) ? $requestData["id"] : null;
        $payload = $this->service->delete($id, ["trip" => $trip_hash]);
        return $this->responder->respond($payload);
    }

}
