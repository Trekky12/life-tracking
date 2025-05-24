<?php

namespace App\Application\Action\Timesheets\SheetFile;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\SheetFile\SheetFileService;
use App\Application\Responder\JSONResultResponder;

class SheetFilesAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, SheetFileService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $sheet_id = $request->getAttribute('sheet');
        
        $payload = $this->service->getFiles($project_hash, $sheet_id);
        return $this->responder->respond($payload);
    }

}
