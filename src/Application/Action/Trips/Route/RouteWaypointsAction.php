<?php

namespace App\Application\Action\Trips\Route;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Route\RouteService;
use App\Application\Responder\JSONResultResponder;

class RouteWaypointsAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, RouteService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $trip_hash = $request->getAttribute("trip");
        $route_id = $request->getQueryParam("route");
        $payload = $this->service->getWaypoints($trip_hash, $route_id);
        return $this->responder->respond($payload);
    }

}
