<?php

namespace App\Application\Action\Crawler\Link;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Crawler\CrawlerLink\CrawlerLinkWriter;

class LinkSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, CrawlerLinkWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_hash = $request->getAttribute("crawler");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["crawler" => $crawler_hash]);
        return $this->responder->respond($entry->withRouteName('crawlers_links')->withRouteParams(["crawler" => $crawler_hash]));
    }

}
