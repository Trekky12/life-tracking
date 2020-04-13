<?php

namespace App\Application\Action\Crawler\Dataset;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerDataset\CrawlerDatasetService;
use App\Application\Responder\HTMLTemplateResponder;

class DatasetSavedListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CrawlerDatasetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_hash = $request->getAttribute('crawler');
        $index = $this->service->saved($crawler_hash);
        return $this->responder->respond($index->withTemplate('crawlers/dataset/saved.twig'));
    }

}
