<?php

namespace App\Application\Action\Timesheets\ProjectCategory;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectCategory\ProjectCategoryService;
use App\Application\Responder\HTMLTemplateResponder;

class ProjectCategoryEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProjectCategoryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($project_hash, $entry_id);
        return $this->responder->respond($data->withTemplate('timesheets/projectcategory/edit.twig'));
    }

}
