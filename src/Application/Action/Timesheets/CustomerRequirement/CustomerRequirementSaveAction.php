<?php

namespace App\Application\Action\Timesheets\CustomerRequirement;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Timesheets\CustomerRequirement\CustomerRequirementWriter;

class CustomerRequirementSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, CustomerRequirementWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $requirementtype_id = $request->getAttribute("requirementtype");
        $id = $request->getAttribute("id");
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["project" => $project_hash, "requirementtype" => $requirementtype_id]);

        $view = $request->getQueryParam('view');
        $route_name = 'timesheets_customers_requirements';
        if ($view == 'calendar') {
            $route_name = 'timesheets_calendar';
        }

        return $this->responder->respond($entry->withRouteName($route_name)->withRouteParams(["project" => $project_hash, "requirementtype" => $requirementtype_id]));
    }
}
