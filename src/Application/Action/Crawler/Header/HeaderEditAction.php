<?php

namespace App\Application\Action\Crawler\Header;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerHeader\CrawlerHeaderService;
use App\Application\Responder\HTMLTemplateResponder;

class HeaderEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CrawlerHeaderService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_hash = $request->getAttribute('crawler');
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($crawler_hash, $entry_id);
        return $this->responder->respond($data->withTemplate('crawlers/headers/edit.twig'));
    }

}
