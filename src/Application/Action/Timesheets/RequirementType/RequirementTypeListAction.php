<?php

namespace App\Application\Action\Timesheets\RequirementType;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\RequirementType\RequirementTypeService;
use App\Application\Responder\HTMLTemplateResponder;

class RequirementTypeListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, RequirementTypeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $index = $this->service->index($project_hash);
        return $this->responder->respond($index->withTemplate('timesheets/requirementtype/index.twig'));
    }

}
