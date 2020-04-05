<?php

namespace App\Application\Action\Splitbill\Bill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\Bill\SplitbillBillService;
use App\Application\Responder\HTMLTemplateResponder;

class BillViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SplitbillBillService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('group');
        $index = $this->service->view($hash);

        return $this->responder->respond($index->withTemplate('splitbills/bills/index.twig'));
    }

}
