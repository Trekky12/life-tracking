<?php

namespace App\Application\Action\Timesheets\CustomerRequirement;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\CustomerRequirement\CustomerRequirementService;
use App\Application\Responder\HTMLTemplateResponder;

class CustomerRequirementListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CustomerRequirementService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $requirementtype_id = $request->getAttribute('requirementtype');
        $requestData = $request->getQueryParams();     
        $data = $this->service->index($project_hash, $requirementtype_id, $requestData);
        return $this->responder->respond($data->withTemplate('timesheets/customerrequirement/index.twig'));
    }

}
