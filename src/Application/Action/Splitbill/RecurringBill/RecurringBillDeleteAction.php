<?php

namespace App\Application\Action\Splitbill\RecurringBill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\RecurringBill\RecurringBillRemover;
use App\Application\Responder\DeleteResponder;

class RecurringBillDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, RecurringBillRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $group_hash = $request->getAttribute("group");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["group" => $group_hash]);
        return $this->responder->respond($payload);
    }

}
