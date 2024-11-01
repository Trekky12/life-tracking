<?php

namespace App\Application\Action\Timesheets\CustomerNotice;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\CustomerNotice\CustomerNoticeService;
use App\Application\Responder\JSONResultResponder;

class CustomerNoticeDataAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, CustomerNoticeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $customer_id = $request->getQueryParam('id');
        
        $payload = $this->service->getData($project_hash, $customer_id);
        return $this->responder->respond($payload);
    }

}
