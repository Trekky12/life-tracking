<?php

namespace App\Application\Action\Timesheets\ProjectCategory;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectCategory\ProjectCategoryRemover;
use App\Application\Responder\DeleteResponder;

class ProjectCategoryDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, ProjectCategoryRemover $service) {
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
