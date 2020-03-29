<?php

namespace App\Application\Action\Car\Car;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\CarRemover;
use App\Application\Responder\DeleteResponder;

class CarDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CarRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
