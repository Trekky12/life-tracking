<?php

namespace App\Application\Action\Finances;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesService;
use App\Application\Responder\HTMLTemplateResponder;
use Dflydev\FigCookies\FigRequestCookies;
use App\Domain\Main\Utility\DateUtility;

class FinancesListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, FinancesService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {

        $requestData = $request->getQueryParams();

        $table_count = FigRequestCookies::get($request, 'perPage_financeTable', 10);
        $table_count_val = intval($table_count->getValue());

        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');

        list($from, $to) = DateUtility::getDateRange($requestData, $defaultFrom);

        $index = $this->service->financeTableIndex($from, $to, $table_count_val);

        return $this->responder->respond($index->withTemplate('finances/index.twig'));
    }

}
