<?php

namespace App\Application\Action\Trips\Route;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Route\RouteRemover;
use App\Application\Responder\DeleteResponder;

class RouteDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, RouteRemover $service) {
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
