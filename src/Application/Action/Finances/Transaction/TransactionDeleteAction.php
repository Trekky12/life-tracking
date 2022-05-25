<?php

namespace App\Application\Action\Finances\Transaction;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Transaction\TransactionRemover;
use App\Application\Responder\DeleteResponder;

class TransactionDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, TransactionRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $account_hash = $request->getAttribute("account");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["account" => $account_hash]);
        return $this->responder->respond($payload);
    }

}
