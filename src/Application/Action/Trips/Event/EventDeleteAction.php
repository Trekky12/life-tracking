<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\EventRemover;
use App\Application\Responder\DeleteResponder;

class EventDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, EventRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $trip_hash = $request->getAttribute("trip");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["trip" => $trip_hash]);
        return $this->responder->respond($payload);
    }

}
