<?php

namespace App\Application\Action\Crawler\Dataset;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerDataset\CrawlerDatasetService;
use App\Application\Responder\JSONResultResponder;

class CrawlerSaveDatasetAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, CrawlerDatasetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('crawler');
        $data = $request->getParsedBody();
        $payload = $this->service->setSave($hash, $data);
        return $this->responder->respond($payload);
    }

}
