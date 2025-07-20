<?php

namespace App\Application\Action\Timesheets\ProjectCategoryBudget;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetService;
use App\Application\Responder\HTMLTemplateResponder;

class ProjectCategoryBudgetListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProjectCategoryBudgetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');

        $is_hidden = $request->getParam('is_hidden', 0);

        $view = $request->getQueryParam('view');

        $index = $this->service->index($project_hash, $view, $is_hidden);
        return $this->responder->respond($index->withTemplate('timesheets/projectcategorybudget/index.twig'));
    }

}
