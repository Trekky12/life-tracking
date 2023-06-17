<?php

namespace App\Application\Action\Car\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarRefuelRemover;
use App\Application\Responder\DeleteResponder;

class RefuelDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CarRefuelRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
