<?php

namespace App\Crawler\CrawlerHeader;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Crawler\CrawlerHeader\CrawlerHeader';
    protected $parent_model = '\App\Crawler\Crawler';
    protected $index_route = 'crawlers_headers';
    protected $edit_template = 'crawlers/headers/edit.twig';
    protected $element_view_route = 'crawlers_headers_edit';
    protected $module = "crawlers";
    private $crawler_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $crawler_id = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_id);

        $this->allowCrawlerOwnerOnly($crawler);

        $headers = $this->mapper->getFromCrawler($crawler->id);
        return $this->ci->view->render($response, 'crawlers/headers/index.twig', ['headers' => $headers, "crawler" => $crawler]);
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

        return $this->ci->view->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'crawler' => $crawler,
                    "sortOptions" => $this->sortOptions(),
                    "castOptions" => $this->castOptions()
        ]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $crawler_hash = $request->getAttribute('crawler');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data, $request);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["crawler" => $crawler_hash]), 301);
    }

    public function clone(Request $request, Response $response) {

        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);
        $crawlers = $this->crawler_mapper->getUserItems('name');

        $this->allowCrawlerOwnerOnly($crawler);

        return $this->ci->view->render($response, 'crawlers/headers/clone.twig', [
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

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["crawler" => $crawler_hash]), 301);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowParentOwnerOnly($id);
    }

    private function allowParentOwnerOnly($element_id) {
        $user = $this->ci->get('helper')->getUser()->id;
        if (!is_null($element_id)) {
            $element = $this->mapper->get($element_id);
            $crawler = $this->crawler_mapper->get($element->crawler);

            if ($crawler->user !== $user) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    private function allowCrawlerOwnerOnly($crawler) {
        $user = $this->ci->get('helper')->getUser()->id;
        if ($crawler->user !== $user) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    private function sortOptions() {
        return [null => $this->ci->get('helper')->getTranslatedString('NO_INITIAL_SORTING'), "asc" => $this->ci->get('helper')->getTranslatedString('ASC'), "desc" => $this->ci->get('helper')->getTranslatedString('DESC')];
    }

    protected function afterSave($id, array $data, Request $request) {
        $header = $this->mapper->get($id);

        // only one header can be initial sorted 
        // so remove the sort value on all others
        if (!is_null($header->sort)) {
            $this->mapper->unset_sort($id, $header->crawler);
        }
    }

    // @see https://dev.mysql.com/doc/refman/8.0/en/cast-functions.html#function_cast
    private function castOptions() {
        return [
            null => $this->ci->get('helper')->getTranslatedString('CAST_NONE'),
            "BINARY" => $this->ci->get('helper')->getTranslatedString('CAST_BINARY'),
            "CHAR" => $this->ci->get('helper')->getTranslatedString('CAST_CHAR'),
            "DATE" => $this->ci->get('helper')->getTranslatedString('CAST_DATE'),
            "DATETIME" => $this->ci->get('helper')->getTranslatedString('CAST_DATETIME'),
            "DECIMAL" => $this->ci->get('helper')->getTranslatedString('CAST_DECIMAL'),
            "SIGNED" => $this->ci->get('helper')->getTranslatedString('CAST_SIGNED'),
            "TIME" => $this->ci->get('helper')->getTranslatedString('CAST_TIME'),
            "UNSIGNED" => $this->ci->get('helper')->getTranslatedString('CAST_UNSIGNED'),
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
