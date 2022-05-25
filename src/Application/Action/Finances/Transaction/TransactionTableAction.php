<?php

namespace App\Application\Action\Finances\Transaction;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Transaction\TransactionService;
use App\Application\Responder\JSONResultResponder;

class TransactionTableAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, TransactionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('account');
        $requestData = $request->getQueryParams();
        $payload = $this->service->table($hash, $requestData);
        return $this->responder->respond($payload);
        
    }

}
