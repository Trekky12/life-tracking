<?php

namespace App\Application\Action\Splitbill\RecurringBill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\RecurringBill\RecurringBillService;
use App\Application\Responder\HTMLTemplateResponder;

class RecurringBillEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, RecurringBillService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $group_hash = $request->getAttribute('group');
        $entry_id = $request->getAttribute('id');
        
        // GET Param 'type'
        $type = $request->getParam('type');
        
        $data = $this->service->edit($group_hash, $entry_id, $type);
        return $this->responder->respond($data->withTemplate('splitbills/recurring/edit.twig'));
    }

}
