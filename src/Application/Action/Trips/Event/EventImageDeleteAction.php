<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\TripEventImageService;
use App\Application\Responder\Trips\EventImageResponder;

class EventImageDeleteAction {

    private $responder;
    private $service;

    public function __construct(EventImageResponder $responder, TripEventImageService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $event_id = $request->getAttribute('id');

        $payload = $this->service->deleteImage($event_id);

        return $this->responder->respond($payload);
    }

}
