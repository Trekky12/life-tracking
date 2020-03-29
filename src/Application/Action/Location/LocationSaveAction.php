<?php

namespace App\Application\Action\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\LocationWriter;
use App\Application\Responder\SaveResponder;

class LocationSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, LocationWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        $payload = $this->service->save($id, $data);

        return $this->responder->respond($payload->withRouteName('location'));
    }

}
