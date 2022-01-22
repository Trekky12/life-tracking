<?php

namespace App\Application\Action\Finances\Transaction;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Transaction\TransactionService;
use App\Application\Responder\HTMLTemplateResponder;

class TransactionListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TransactionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('account');
        $index = $this->service->index($hash);
        return $this->responder->respond($index->withTemplate('finances/transaction/index.twig'));
    }

}
