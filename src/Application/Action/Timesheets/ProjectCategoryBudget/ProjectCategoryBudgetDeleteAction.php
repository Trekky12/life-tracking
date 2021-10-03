<?php

namespace App\Application\Action\Timesheets\ProjectCategoryBudget;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetRemover;
use App\Application\Responder\DeleteResponder;

class ProjectCategoryBudgetDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, ProjectCategoryBudgetRemover $service) {
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
