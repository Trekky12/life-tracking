<?php

namespace App\Application\Action\Crawler\Link;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerLink\CrawlerLinkRemover;
use App\Application\Responder\DeleteResponder;

class LinkDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CrawlerLinkRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_hash = $request->getAttribute("crawler");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["crawler" => $crawler_hash]);
        return $this->responder->respond($payload);
    }

}
