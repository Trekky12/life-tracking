<?php

namespace App\Application\Action\Car\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarServiceRemover;
use App\Application\Responder\DeleteResponder;

class ServiceDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CarServiceRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $car_hash = $request->getAttribute('car');
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["car" => $car_hash]);
        return $this->responder->respond($payload);
    }

}
