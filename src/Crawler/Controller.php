<?php

namespace App\Crawler;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Main\Utility\DateUtility;
use App\User\UserService;

class Controller extends \App\Base\Controller {

    private $link_service;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CrawlerService $service,
            CrawlerLink\CrawlerLinkService $link_service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->link_service = $link_service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {
        $crawlers = $this->service->getCrawlersOfUser();
        return $this->twig->render($response, 'crawlers/index.twig', ['crawlers' => $crawlers]);
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        if ($this->service->isOwner($entry_id) === false) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return $this->twig->render($response, 'crawlers/edit.twig', ['entry' => $entry, 'users' => $users]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        if ($this->service->isOwner($id) === false) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $new_id = $this->doSave($id, $data, null);

        $this->service->setHash($new_id);

        $redirect_url = $this->router->urlFor('crawlers');
        return $response->withRedirect($redirect_url, 301);
    }

    public function view(Request $request, Response $response) {

        $data = $request->getQueryParams();

        $hash = $request->getAttribute('crawler');

        $crawler = $this->service->getFromHash($hash);

        if (!$this->service->isMember($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        list($from, $to) = DateUtility::getDateRange($data);
        $response_data = $this->service->view($crawler, $from, $to);

        $links = $this->link_service->getFromCrawler($crawler->id, 'position');
        $response_data["links"] = $this->link_service->buildTree($links);

        return $this->twig->render($response, 'crawlers/view.twig', $response_data);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $hash = $request->getAttribute('crawler');

        $crawler = $this->service->getFromHash($hash);

        if (!$this->service->isMember($crawler->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        list($from, $to) = DateUtility::getDateRange($requestData);
        $response_data = $this->service->table($crawler, $from, $to, $requestData);

        return $response->withJson($response_data);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        if ($this->service->isOwner($id) === false) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

    public function setFilter(Request $request, Response $response) {

        $data = $request->getParsedBody();
        $hash = $request->getAttribute('crawler');

        if (!is_null($hash)) {
            $crawler = $this->service->getFromHash($hash);

            if (!$this->service->isMember($crawler->id)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            $set_filter = $this->service->setFilter($crawler, $data);

            if ($set_filter) {
                $response_data = ['status' => 'success'];
                return $response->withJSON($response_data);
            }
        }
        $response_data = ['status' => 'error'];
        return $response->withJSON($response_data);
    }

}
