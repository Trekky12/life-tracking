<?php

namespace App\Application\Action\Finances\TransactionRecurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\TransactionRecurring\TransactionRecurringService;
use App\Application\Responder\HTMLTemplateResponder;

class TransactionRecurringListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TransactionRecurringService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('finances/transaction/recurring/index.twig'));
    }

}
