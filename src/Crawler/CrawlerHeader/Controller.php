<?php

namespace App\Crawler\CrawlerHeader;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Main\UserHelper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Crawler\CrawlerHeader\CrawlerHeader';
    protected $parent_model = '\App\Crawler\Crawler';
    protected $index_route = 'crawlers_headers';
    protected $edit_template = 'crawlers/headers/edit.twig';
    protected $element_view_route = 'crawlers_headers_edit';
    protected $module = "crawlers";
    private $crawler_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, UserHelper $user_helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation) {
        parent::__construct($logger, $twig, $helper, $user_helper, $flash, $router, $settings, $db, $activity, $translation);

        $user = $this->user_helper->getUser();

        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $crawler_id = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_id);

        $this->allowCrawlerOwnerOnly($crawler);

        $headers = $this->mapper->getFromCrawler($crawler->id);
        return $this->twig->render($response, 'crawlers/headers/index.twig', ['headers' => $headers, "crawler" => $crawler]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');
        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $this->preEdit($entry_id, $request);

        return $this->twig->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'crawler' => $crawler,
                    "sortOptions" => $this->sortOptions(),
                    "castOptions" => $this->castOptions()
        ]);
    }

    public function clone(Request $request, Response $response) {

        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);
        $crawlers = $this->crawler_mapper->getUserItems('name');

        $this->allowCrawlerOwnerOnly($crawler);

        return $this->twig->render($response, 'crawlers/headers/clone.twig', [
                    'crawler' => $crawler,
                    'crawlers' => $crawlers
        ]);
    }

    public function cloning(Request $request, Response $response) {

        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);

        $clone_id = $request->getParam('clone');
        $clone_crawler = $this->crawler_mapper->get($clone_id);

        $this->allowCrawlerOwnerOnly($crawler);
        $this->allowCrawlerOwnerOnly($clone_crawler);

        $clone_elements = $this->mapper->getFromCrawler($clone_id);
        foreach ($clone_elements as &$clone) {
            $fromID = $clone->id;
            $clone->crawler = $crawler->id;
            $clone->id = null;
            $id = $this->mapper->insert($clone);

            $this->logger->addNotice("Duplicate crawler headline", array("from" => $clone_crawler->id, "to" => $crawler->id, "fromID" => $fromID, "toID" => $id));
        }

        return $response->withRedirect($this->router->urlFor($this->index_route, ["crawler" => $crawler_hash]), 301);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $crawler_hash = $request->getAttribute("crawler");
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);
        $this->allowCrawlerOwnerOnly($crawler);

        $data['crawler'] = $crawler->id;
    }

    protected function preEdit($id, Request $request) {
        $crawler_hash = $request->getAttribute("crawler");
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);
        $this->allowCrawlerOwnerOnly($crawler);
    }

    protected function preDelete($id, Request $request) {
        $crawler_hash = $request->getAttribute("crawler");
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);
        $this->allowCrawlerOwnerOnly($crawler);
    }

    private function allowCrawlerOwnerOnly($crawler) {
        $user = $this->user_helper->getUser()->id;
        if ($crawler->user !== $user) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    private function sortOptions() {
        return [null => $this->translation->getTranslatedString('NO_INITIAL_SORTING'), "asc" => $this->translation->getTranslatedString('ASC'), "desc" => $this->translation->getTranslatedString('DESC')];
    }

    protected function afterSave($id, array $data, Request $request) {
        $header = $this->mapper->get($id);

        // only one header can be initial sorted 
        // so remove the sort value on all others
        if (!is_null($header->sort)) {
            $this->mapper->unset_sort($id, $header->crawler);
        }

        $crawler_id = $header->crawler;
        $crawler = $this->crawler_mapper->get($crawler_id);
        $this->index_params = ["crawler" => $crawler->getHash()];
    }

    // @see https://dev.mysql.com/doc/refman/8.0/en/cast-functions.html#function_cast
    private function castOptions() {
        return [
            null => $this->translation->getTranslatedString('CAST_NONE'),
            "BINARY" => $this->translation->getTranslatedString('CAST_BINARY'),
            "CHAR" => $this->translation->getTranslatedString('CAST_CHAR'),
            "DATE" => $this->translation->getTranslatedString('CAST_DATE'),
            "DATETIME" => $this->translation->getTranslatedString('CAST_DATETIME'),
            "DECIMAL" => $this->translation->getTranslatedString('CAST_DECIMAL'),
            "SIGNED" => $this->translation->getTranslatedString('CAST_SIGNED'),
            "TIME" => $this->translation->getTranslatedString('CAST_TIME'),
            "UNSIGNED" => $this->translation->getTranslatedString('CAST_UNSIGNED'),
        ];
    }

    protected function getElementViewRoute($entry) {
        $crawler = $this->getParentObjectMapper()->get($entry->getParentID());
        $this->element_view_route_params["crawler"] = $crawler->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->crawler_mapper;
    }

}
