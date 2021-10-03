<?php

namespace App\Application\Action\Timesheets\ProjectCategoryBudget;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetWriter;

class ProjectCategoryBudgetSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, ProjectCategoryBudgetWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["project" => $project_hash]);
        return $this->responder->respond($entry->withRouteName('timesheets_project_categorybudget')->withRouteParams(["project" => $project_hash]));
    }

}
