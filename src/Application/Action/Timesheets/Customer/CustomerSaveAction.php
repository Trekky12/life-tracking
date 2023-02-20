<?php

namespace App\Application\Action\Timesheets\Customer;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Timesheets\Customer\CustomerWriter;

class CustomerSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, CustomerWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["project" => $project_hash]);
        return $this->responder->respond($entry->withRouteName('timesheets_customers')->withRouteParams(["project" => $project_hash]));
    }

}
