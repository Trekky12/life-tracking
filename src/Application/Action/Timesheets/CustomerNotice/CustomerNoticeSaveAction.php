<?php

namespace App\Application\Action\Timesheets\CustomerNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Timesheets\CustomerNotice\CustomerNoticeWriter;

class CustomerNoticeSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, CustomerNoticeWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $customer_id = $request->getAttribute("customer");
        $id = $request->getAttribute("id");
        $data = $request->getParsedBody();
        $payload = $this->service->save($id, $data, ["project" => $project_hash, "customer" => $customer_id]);
        return $this->responder->respond($payload);
    }

}
