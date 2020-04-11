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

        // Date Filter
        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');
        list($from, $to) = DateUtility::getDateRange($requestData, $defaultFrom);

        $index = $this->service->view($hash, $from, $to);

        return $this->responder->respond($index->withTemplate('timesheets/sheets/index.twig'));
    }

}
