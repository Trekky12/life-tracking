<?php

namespace App\Application\Action\Timesheets\SheetFile;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\SheetFile\SheetFileWriter;
use App\Application\Responder\ImageResponder;

class SheetFileSaveAction {

    private $responder;
    private $service;

    public function __construct(ImageResponder $responder, SheetFileWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $sheet_id = $request->getAttribute('sheet');
        $files = $request->getUploadedFiles();
        
        $data = $request->getParsedBody();
        
        $payload = $this->service->save(null, $data, ["project" => $project_hash, "sheet" => $sheet_id, "files" => $files]);

        return $this->responder->respond($payload);
    }
}
