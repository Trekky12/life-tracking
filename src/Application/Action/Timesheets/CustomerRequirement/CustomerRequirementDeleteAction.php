<?php

namespace App\Application\Action\Timesheets\CustomerRequirement;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\CustomerRequirement\CustomerRequirementRemover;
use App\Application\Responder\DeleteResponder;

class CustomerRequirementDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CustomerRequirementRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $requirementtype_id = $request->getAttribute("requirementtype");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["project" => $project_hash, "requirementtype" => $requirementtype_id]);
        return $this->responder->respond($payload);
    }
}
