<?php

namespace App\Application\Action\Crawler\Crawler;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerService;
use App\Application\Responder\HTMLTemplateResponder;

class CrawlerListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CrawlerService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $archive = $request->getParam('archive', 0);
        $index = $this->service->index($archive);
        return $this->responder->respond($index->withTemplate('crawlers/index.twig'));
    }

}
