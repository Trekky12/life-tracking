<?php

namespace App\Application\Action\Finances\TransactionRecurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Finances\TransactionRecurring\TransactionRecurringWriter;

class TransactionRecurringSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, TransactionRecurringWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('finances_transaction_recurring'));
    }

}
