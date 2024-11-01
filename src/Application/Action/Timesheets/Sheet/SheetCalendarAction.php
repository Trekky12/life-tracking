<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class SheetCalendarAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SheetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');

        $requestData = $request->getQueryParams();

        list($from, $to) = DateUtility::getDateRange($requestData, null, null); //, $defaultFrom);

        $categories = array_key_exists("categories", $requestData) ? filter_var_array($requestData["categories"], FILTER_SANITIZE_NUMBER_INT) : [];

        $billed = array_key_exists('billed', $requestData) && $requestData['billed'] !== '' ? intval(filter_var($requestData['billed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $payed = array_key_exists('payed', $requestData) && $requestData['payed'] !== '' ? intval(filter_var($requestData['payed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $planned = array_key_exists('planned', $requestData) && $requestData['planned'] !== '' ? intval(filter_var($requestData['planned'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $customer = array_key_exists('customer', $requestData) && $requestData['customer'] !== '' ? intval(filter_var($requestData['customer'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $index = $this->service->calendar($hash, $from, $to, $categories, $billed, $payed, $planned, $customer);

        //$print = array_key_exists('print', $requestData) && $requestData['print'] !== '' ? (filter_var($requestData['print'], FILTER_SANITIZE_NUMBER_INT)) == 1 : false;
        //$template = $print ? 'timesheets/sheets/calendar-print.twig' : 'timesheets/sheets/calendar.twig';

        return $this->responder->respond($index->withTemplate('timesheets/sheets/calendar.twig'));
    }
}
