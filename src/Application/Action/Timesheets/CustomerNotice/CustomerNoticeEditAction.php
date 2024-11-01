<?php

namespace App\Application\Action\Timesheets\CustomerNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\CustomerNotice\CustomerNoticeService;
use App\Application\Responder\HTMLTemplateResponder;

class CustomerNoticeEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CustomerNoticeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $customer_id = $request->getAttribute('customer');
        $requestData = $request->getQueryParams();     
        $data = $this->service->edit($project_hash, $customer_id, $requestData);
        return $this->responder->respond($data->withTemplate('timesheets/customer/notice.twig'));
    }

}
