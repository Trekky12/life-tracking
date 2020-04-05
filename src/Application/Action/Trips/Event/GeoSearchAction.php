<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\TripEventService;
use App\Application\Responder\JSONResultResponder;

class GeoSearchAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, TripEventService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getQueryParams();

        $payload = $this->service->getLatLng($data);

        return $this->responder->respond($payload);
    }

}
