<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetExportService;
use App\Application\Responder\Download\DownloadResponder;

class SheetExportAction {

    private $responder;
    private $service;

    public function __construct(DownloadResponder $responder, SheetExportService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');

        $data = $request->getQueryParams();

        $payload = $this->service->export($hash, $data);
        return $this->responder->respond($payload);
    }

}
