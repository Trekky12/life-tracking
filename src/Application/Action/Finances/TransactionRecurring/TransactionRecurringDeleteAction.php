<?php

namespace App\Application\Action\Finances\TransactionRecurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\TransactionRecurring\TransactionRecurringRemover;
use App\Application\Responder\DeleteResponder;

class TransactionRecurringDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, TransactionRecurringRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
