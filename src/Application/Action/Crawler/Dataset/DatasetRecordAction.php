<?php

namespace App\Application\Action\Crawler\Dataset;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerDataset\CrawlerDatasetWriter;
use App\Application\Responder\SaveJSONResponder;

class DatasetRecordAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, CrawlerDatasetWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        //$crawler_hash = $request->getAttribute('crawler');
        $crawler_hash = $request->getParam('crawler');
        $data = $request->getParsedBody();
        
        $payload = $this->service->save(null, $data, ["crawler" => $crawler_hash]);

        return $this->responder->respond($payload);
    }

}
