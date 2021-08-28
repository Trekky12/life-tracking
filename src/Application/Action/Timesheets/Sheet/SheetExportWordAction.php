<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetExportWordService;
use App\Application\Responder\Word\WordExportResponder;
use App\Domain\Main\Utility\DateUtility;

class SheetExportWordAction {

    private $responder;
    private $service;

    public function __construct(WordExportResponder $responder, SheetExportWordService $service) {
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
