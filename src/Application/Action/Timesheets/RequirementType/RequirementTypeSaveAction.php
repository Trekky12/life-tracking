<?php

namespace App\Application\Action\Timesheets\RequirementType;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Timesheets\RequirementType\RequirementTypeWriter;

class RequirementTypeSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, RequirementTypeWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["project" => $project_hash]);
        return $this->responder->respond($entry->withRouteName('timesheets_requirement_types')->withRouteParams(["project" => $project_hash]));
    }

}
