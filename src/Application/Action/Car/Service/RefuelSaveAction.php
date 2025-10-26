<?php

namespace App\Application\Action\Car\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Car\Service\CarRefuelWriter;

class RefuelSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, CarRefuelWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $car_hash = $request->getAttribute('car');
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["car" => $car_hash]);

        return $this->responder->respond($entry->withRouteName('car_service_refuel')->withRouteParams(["car" => $car_hash]));
    }

}
