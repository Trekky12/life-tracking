<?php

namespace App\Application\Action\Timesheets\ProjectNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Timesheets\ProjectNotice\ProjectNoticeWriter;

class ProjectNoticeSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, ProjectNoticeWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute("id");
        $data = $request->getParsedBody();
        $payload = $this->service->save($id, $data, ["project" => $project_hash]);
        return $this->responder->respond($payload);
    }

}
