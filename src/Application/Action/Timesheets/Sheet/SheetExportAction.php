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

        $type = array_key_exists("type", $requestData) ? Utility::filter_string_polyfill($requestData["type"]) : null;

        list($from, $to) = DateUtility::getDateRange($requestData);
        $categories = array_key_exists("categories", $requestData) ? filter_var_array($requestData["categories"], FILTER_SANITIZE_NUMBER_INT) : [];

        $invoiced = array_key_exists('invoiced', $requestData) && $requestData['invoiced'] !== '' ? intval(filter_var($requestData['invoiced'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $billed = array_key_exists('billed', $requestData) && $requestData['billed'] !== '' ? intval(filter_var($requestData['billed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $payed = array_key_exists('payed', $requestData) && $requestData['payed'] !== '' ? intval(filter_var($requestData['payed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $happened = array_key_exists('happened', $requestData) && $requestData['happened'] !== '' ? intval(filter_var($requestData['happened'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $customer = array_key_exists('customer', $requestData) && $requestData['customer'] !== '' ? intval(filter_var($requestData['customer'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $noticefields = array_key_exists("noticefields", $requestData) ? filter_var_array($requestData["noticefields"], FILTER_SANITIZE_NUMBER_INT) : [];

        $payload = $this->service->export($hash, $type, $from, $to, $categories, $invoiced, $billed, $payed, $happened, $customer, $noticefields);

        $template = 'timesheets/sheets/export-html.twig';
        if (strcmp($type ?? '', "html-overview") == 0) {
            $template = 'timesheets/sheets/export-html-overview.twig';
        }
        return $this->responder->respond($payload->withTemplate($template));
    }
}
