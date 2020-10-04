<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetExportService;
use App\Application\Responder\Excel\ExcelExportResponder;
use App\Domain\Main\Utility\DateUtility;

class SheetExportAction {

    private $responder;
    private $service;

    public function __construct(ExcelExportResponder $responder, SheetExportService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");

        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData);

        $payload = $this->service->export($project_hash, $from, $to);
        return $this->responder->respond($payload);
    }

}
