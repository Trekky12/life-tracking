<?php

namespace App\Application\Action\Finances;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\FinancesService;
use App\Application\Responder\JSONResponder;
use App\Domain\Main\Utility\DateUtility;

class FinancesTableAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, FinancesService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData);
        $payload = $this->service->table($from, $to, $requestData);
        return $this->responder->respond($payload);
        
    }

}
