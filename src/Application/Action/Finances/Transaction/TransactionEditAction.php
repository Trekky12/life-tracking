<?php

namespace App\Application\Action\Finances\Transaction;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Transaction\TransactionService;
use App\Application\Responder\HTMLTemplateResponder;

class TransactionEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TransactionService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $account_hash = $request->getQueryParam('account');
        $data = $this->service->edit($entry_id, $account_hash);
        return $this->responder->respond($data->withTemplate('finances/transaction/edit.twig'));
    }

}
