<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class SheetViewAction {

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
        $payed = array_key_exists('payed', $requestData) && $requestData['payed']!== '' ? intval(filter_var($requestData['payed'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $index = $this->service->view($hash, $from, $to, $categories, $billed, $payed);

        return $this->responder->respond($index->withTemplate('timesheets/sheets/index.twig'));
    }

}
