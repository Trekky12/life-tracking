<?php

namespace App\Application\Action\Timesheets\Project;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Project\ProjectService;
use App\Application\Responder\HTMLTemplateResponder;

class ProjectEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProjectService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('timesheets/projects/edit.twig'));
    }

}
