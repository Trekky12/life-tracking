<?php

namespace App\Application\Action\Timesheets\SheetNotice;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Timesheets\SheetNotice\SheetNoticeWriter;

class SheetNoticeSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, SheetNoticeWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $sheet_id = $request->getAttribute("sheet");
        $id = $request->getAttribute("id");
        $data = $request->getParsedBody();
        $payload = $this->service->save($id, $data, ["project" => $project_hash, "sheet" => $sheet_id]);
        return $this->responder->respond($payload);
    }

}
