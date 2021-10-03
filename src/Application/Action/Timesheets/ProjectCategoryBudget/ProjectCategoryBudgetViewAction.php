<?php

namespace App\Application\Action\Timesheets\ProjectCategoryBudget;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetService;
use App\Application\Responder\HTMLTemplateResponder;

class ProjectCategoryBudgetViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProjectCategoryBudgetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $index = $this->service->view($project_hash);
        return $this->responder->respond($index->withTemplate('timesheets/projectcategorybudget/view.twig'));
    }

}
