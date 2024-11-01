<?php

namespace App\Application\Action\Timesheets\SheetNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\SheetNotice\SheetNoticeService;
use App\Application\Responder\HTMLTemplateResponder;

class SheetNoticeViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SheetNoticeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $sheet_id = $request->getAttribute('sheet');
        $requestData = $request->getQueryParams();     
        $data = $this->service->edit($project_hash, $sheet_id, $requestData);
        return $this->responder->respond($data->withTemplate('timesheets/sheets/notice-view.twig'));
    }

}
