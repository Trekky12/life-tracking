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

        $index = $this->service->view($hash, $from, $to, $categories);

        return $this->responder->respond($index->withTemplate('timesheets/sheets/index.twig'));
    }

}
