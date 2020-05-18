<?php

namespace App\Application\Action\Splitbill\RecurringBill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\RedirectResponder;
use App\Domain\Splitbill\RecurringBill\RecurringBillEntryCreator;

class RecurringBillTriggerAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, RecurringBillEntryCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $group_hash = $request->getAttribute("group");
        $id = $request->getAttribute('id');
        $entry = $this->service->createEntry($id);
        
        return $this->responder->respond('splitbill_bills', 301, true, ["group" => $group_hash]);
    }

}
