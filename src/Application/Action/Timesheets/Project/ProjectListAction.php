<?php

namespace App\Application\Action\Timesheets\Project;

use Slim\Http\ServerRequest as Request;
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
        $archive = $request->getParam('archive', 0);
        $index = $this->service->index($archive);
        return $this->responder->respond($index->withTemplate('timesheets/projects/index.twig'));
    }

}
