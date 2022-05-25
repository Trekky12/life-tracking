<?php

namespace App\Application\Action\Timesheets\SheetNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\SheetNotice\SheetNoticeService;
use App\Application\Responder\JSONResultResponder;

class SheetNoticeDataAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, SheetNoticeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $sheet_id = $request->getQueryParam('sheet');
        
        $payload = $this->service->getData($project_hash, $sheet_id);
        return $this->responder->respond($payload);
    }

}
