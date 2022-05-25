<?php

namespace App\Application\Action\Finances\TransactionRecurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\RedirectResponder;
use App\Domain\Finances\TransactionRecurring\RecurringTransactionCreator;

class TransactionRecurringTriggerAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, RecurringTransactionCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $entry = $this->service->createEntry($id);
        
        return $this->responder->respond('finances_account', 301, true);
    }

}
