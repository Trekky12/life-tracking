<?php

namespace App\Application\Action\Timesheets\SheetFile;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\SheetFile\SheetFileRemover;
use App\Application\Responder\DeleteJSONResponder;

class SheetFileDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteJSONResponder $responder, SheetFileRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id =  $request->getQueryParam('id');
        $project_hash = $request->getAttribute('project');
        $sheet_id = $request->getAttribute('sheet');

        $payload = $this->service->delete($id, ["project" => $project_hash, "sheet" => $sheet_id]);

        return $this->responder->respond($payload);
    }
}
