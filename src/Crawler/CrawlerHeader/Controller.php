<?php

namespace App\Crawler\CrawlerHeader;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Crawler\CrawlerService;

class Controller extends \App\Base\Controller {

    private $crawler_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CrawlerHeaderService $service,
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

        $headers = $this->service->getFromCrawler($crawler->id);
        return $this->twig->render($response, 'crawlers/headers/index.twig', ['headers' => $headers, "crawler" => $crawler]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');
        $crawler_hash = $request->getAttribute('crawler');

        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);

        return $this->twig->render($response, 'crawlers/headers/edit.twig', [
                    'entry' => $entry,
                    'crawler' => $crawler,
                    "sortOptions" => $this->service->getSortOptions(),
                    "castOptions" => $this->service->getCastOptions()
        ]);
    }

    public function clone(Request $request, Response $response) {

        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_service->getFromHash($crawler_hash);
        $crawlers = $this->crawler_service->getCrawlersOfUser();

        if (!$this->crawler_service->isOwner($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        return $this->twig->render($response, 'crawlers/headers/clone.twig', [
                    'crawler' => $crawler,
                    'crawlers' => $crawlers
        ]);
    }

    public function cloning(Request $request, Response $response) {

        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_service->getFromHash($crawler_hash);

        $clone_id = $request->getParam('clone');
        $clone_crawler = $this->crawler_service->getEntry($clone_id);

        if (!$this->crawler_service->isOwner($crawler->id) && !$this->crawler_service->isOwner($clone_crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $this->service->cloneHeaders($clone_crawler, $crawler);

        return $response->withRedirect($this->router->urlFor('crawlers_headers', ["crawler" => $crawler_hash]), 301);
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

        $this->service->unsetSortingForOtherHeaders($new_id);

        $redirect_url = $this->router->urlFor('crawlers_headers', ["crawler" => $crawler_hash]);
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
