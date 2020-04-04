<?php

namespace App\Application\Action\Crawler\Link;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerLink\CrawlerLinkService;
use App\Application\Responder\HTMLTemplateResponder;

class LinkEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CrawlerLinkService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_hash = $request->getAttribute('crawler');
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($crawler_hash, $entry_id);
        return $this->responder->respond($data->withTemplate('crawlers/links/edit.twig'));
    }

}
