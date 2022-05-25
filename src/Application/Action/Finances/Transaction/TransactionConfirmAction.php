<?php

namespace App\Application\Action\Finances\Transaction;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Transaction\TransactionService;
use App\Application\Responder\JSONResultResponder;

class TransactionConfirmAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, TransactionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->setConfirmed($data);
        return $this->responder->respond($payload);
    }

}
