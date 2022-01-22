<?php

namespace App\Application\Action\Finances\Transaction;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Transaction\TransactionService;
use App\Application\Responder\HTMLTemplateResponder;

class TransactionViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TransactionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->view($entry_id);
        return $this->responder->respond($data->withTemplate('finances/transaction/view.twig'));
    }

}
