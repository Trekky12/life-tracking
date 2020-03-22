<?php

namespace App\Domain\Crawler\CrawlerLink;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Crawler\CrawlerService;

class Controller extends \App\Domain\Base\Controller {

    private $crawler_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CrawlerLinkService $service,
            CrawlerService $crawler_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->crawler_service = $crawler_service;

        $this->service->setParentObjectService($crawler_service);
    }

    public function index(Request $request, Response $response) {
        $crawler_hash = $request->getAttribute('crawler');

        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $links = $this->service->getFromCrawler($crawler->id);
        return $this->twig->render($response, 'crawlers/links/index.twig', ['links' => $links, "crawler" => $crawler]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');
        $crawler_hash = $request->getAttribute('crawler');

        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);

        $links = $this->service->getFromCrawler($crawler->id, 'position');

        return $this->twig->render($response, 'crawlers/links/edit.twig', ['entry' => $entry, 'crawler' => $crawler, 'links' => $links]);
    }
    
    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        
        $crawler_hash = $request->getAttribute("crawler");
        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data['crawler'] = $crawler->id;

        $new_id = $this->doSave($id, $data, null);

        $redirect_url = $this->router->urlFor('crawlers_links', ["crawler" => $crawler_hash]);
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        $crawler_hash = $request->getAttribute("crawler");
        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

}
