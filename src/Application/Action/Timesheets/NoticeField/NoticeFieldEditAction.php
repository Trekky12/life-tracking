<?php

namespace App\Application\Action\Timesheets\NoticeField;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\NoticeField\NoticeFieldService;
use App\Application\Responder\HTMLTemplateResponder;

class NoticeFieldEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, NoticeFieldService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($project_hash, $entry_id);
        return $this->responder->respond($data->withTemplate('timesheets/noticefield/edit.twig'));
    }

}
