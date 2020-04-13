<?php

namespace App\Application\Action\Splitbill\RecurringBill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Splitbill\RecurringBill\RecurringBillWriter;

class RecurringBillSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, RecurringBillWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $group_hash = $request->getAttribute("group");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["group" => $group_hash]);
        return $this->responder->respond($entry->withRouteName('splitbill_bills_recurring')->withRouteParams(["group" => $group_hash]));
    }

}
