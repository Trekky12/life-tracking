<?php

namespace App\Application\Action\Crawler\Crawler;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class CrawlerViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CrawlerService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('crawler');
        $data = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($data, null);
        
        $index = $this->service->view($hash, $from, $to);

        return $this->responder->respond($index->withTemplate('crawlers/view.twig'));
    }

}
