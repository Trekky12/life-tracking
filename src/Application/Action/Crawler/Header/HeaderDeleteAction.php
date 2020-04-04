<?php

namespace App\Application\Action\Crawler\Header;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerHeader\CrawlerHeaderRemover;
use App\Application\Responder\DeleteResponder;

class HeaderDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CrawlerHeaderRemover $service) {
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
