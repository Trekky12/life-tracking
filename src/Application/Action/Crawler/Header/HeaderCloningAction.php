<?php

namespace App\Application\Action\Crawler\Header;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Crawler\CrawlerHeader\CrawlerHeaderService;
use App\Application\Responder\RedirectResponder;

class HeaderCloningAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, CrawlerHeaderService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $crawler_destination_hash = $request->getAttribute('crawler');
        $crawler_target_id = $request->getParam('clone');
        
        $payload = $this->service->clone($crawler_destination_hash, $crawler_target_id);
        
        return $this->responder->respond('crawlers_headers', 301, true, ["crawler" => $crawler_destination_hash]);
    }

}
