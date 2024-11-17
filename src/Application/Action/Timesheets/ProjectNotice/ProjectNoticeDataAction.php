<?php

namespace App\Application\Action\Timesheets\ProjectNotice;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\ProjectNotice\ProjectNoticeService;
use App\Application\Responder\JSONResultResponder;

class ProjectNoticeDataAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, ProjectNoticeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');

        $payload = $this->service->getData($project_hash);
        return $this->responder->respond($payload);
    }
}
