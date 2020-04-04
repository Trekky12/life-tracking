<?php

namespace App\Application\Action\Crawler\Crawler;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerService;
use App\Application\Responder\JSONResultResponder;

class CrawlerSetFilterAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, CrawlerService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('crawler');
        $data = $request->getParsedBody();
        $payload = $this->service->setFilter($hash, $data);
        return $this->responder->respond($payload);
    }

}
