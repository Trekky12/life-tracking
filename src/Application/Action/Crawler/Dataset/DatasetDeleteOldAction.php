<?php

namespace App\Application\Action\Crawler\Dataset;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerDataset\CrawlerDatasetService;
use App\Application\Responder\Crawlers\CrawlersDeleteOldResponder;

class DatasetDeleteOldAction {

    private $responder;
    private $service;

    public function __construct(CrawlersDeleteOldResponder $responder, CrawlerDatasetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_id = $request->getAttribute('id');
        $payload = $this->service->deleteOldEntries($crawler_id);
        return $this->responder->respond($payload);
    }

}
