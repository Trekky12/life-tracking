<?php

namespace App\Application\Action\Timesheets\Project;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Project\ProjectService;
use App\Application\Responder\HTMLTemplateResponder;

class ProjectListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProjectService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('timesheets/projects/index.twig'));
    }

}
