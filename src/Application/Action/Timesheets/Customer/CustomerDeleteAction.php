<?php

namespace App\Application\Action\Timesheets\Customer;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Customer\CustomerRemover;
use App\Application\Responder\DeleteResponder;

class CustomerDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CustomerRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["project" => $project_hash]);
        return $this->responder->respond($payload);
    }

}
