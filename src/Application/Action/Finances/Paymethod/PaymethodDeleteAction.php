<?php

namespace App\Application\Action\Finances\Paymethod;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Paymethod\PaymethodRemover;
use App\Application\Responder\DeleteResponder;

class PaymethodDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, PaymethodRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
