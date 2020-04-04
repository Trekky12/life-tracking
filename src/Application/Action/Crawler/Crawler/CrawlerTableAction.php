<?php

namespace App\Application\Action\Crawler\Crawler;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerService;
use App\Application\Responder\JSONResultResponder;
use App\Domain\Main\Utility\DateUtility;

class CrawlerTableAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, CrawlerService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('crawler');
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData);
        $payload = $this->service->table($hash, $from, $to, $requestData);
        return $this->responder->respond($payload);
        
    }

}
