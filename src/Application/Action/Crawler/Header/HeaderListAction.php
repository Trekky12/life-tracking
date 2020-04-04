<?php

namespace App\Application\Action\Crawler\Header;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerHeader\CrawlerHeaderService;
use App\Application\Responder\HTMLTemplateResponder;

class HeaderListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CrawlerHeaderService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_hash = $request->getAttribute('crawler');
        $index = $this->service->index($crawler_hash);
        return $this->responder->respond($index->withTemplate('crawlers/headers/index.twig'));
    }

}
