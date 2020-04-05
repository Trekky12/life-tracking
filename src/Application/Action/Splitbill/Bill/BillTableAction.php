<?php

namespace App\Application\Action\Splitbill\Bill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\Bill\SplitbillBillService;
use App\Application\Responder\JSONResultResponder;

class BillTableAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, SplitbillBillService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('group');
        $requestData = $request->getQueryParams();
        $payload = $this->service->table($hash, $requestData);
        return $this->responder->respond($payload);
        
    }

}
