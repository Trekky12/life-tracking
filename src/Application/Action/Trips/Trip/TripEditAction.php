<?php

namespace App\Application\Action\Trips\Trip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\TripService;
use App\Application\Responder\HTMLTemplateResponder;

class TripEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TripService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('trips/edit.twig'));
    }

}
