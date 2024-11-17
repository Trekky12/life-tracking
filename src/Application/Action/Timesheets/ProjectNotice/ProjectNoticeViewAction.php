<?php

namespace App\Application\Action\Timesheets\ProjectNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectNotice\ProjectNoticeService;
use App\Application\Responder\HTMLTemplateResponder;

class ProjectNoticeViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProjectNoticeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $requestData = $request->getQueryParams();
        $data = $this->service->edit($project_hash, $requestData);
        return $this->responder->respond($data->withTemplate('timesheets/projects/notice-view.twig'));
    }
}
