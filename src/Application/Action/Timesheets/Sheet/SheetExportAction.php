<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetExportService;
use App\Application\Responder\Download\DownloadResponder;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\Main\Utility\Utility;

class SheetExportAction {

    private $responder;
    private $service;

    public function __construct(DownloadResponder $responder, SheetExportService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');

        $requestData = $request->getQueryParams();

        $payload = $this->service->export($hash, $requestData);
        $result = $payload->getResult();

        $template = 'timesheets/sheets/export-html.twig';
        if (is_array($result) && array_key_exists("type", $result) && strcmp($result['type'] ?? '', "html-overview") == 0) {
            $template = 'timesheets/sheets/export-html-overview.twig';
        }
        return $this->responder->respond($payload->withTemplate($template));
    }
}
