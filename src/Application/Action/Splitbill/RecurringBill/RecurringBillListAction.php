<?php

namespace App\Application\Action\Splitbill\RecurringBill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\RecurringBill\RecurringBillService;
use App\Application\Responder\HTMLTemplateResponder;

class RecurringBillListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, RecurringBillService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $group_hash = $request->getAttribute('group');
        $index = $this->service->index($group_hash);
        return $this->responder->respond($index->withTemplate('splitbills/recurring/index.twig'));
    }

}
