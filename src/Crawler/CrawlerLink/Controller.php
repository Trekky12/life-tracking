<?php

namespace App\Crawler\CrawlerLink;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Crawler\CrawlerLink\CrawlerLink';
    protected $parent_model = '\App\Crawler\Crawler';
    protected $index_route = 'crawlers_links';
    protected $edit_template = 'crawlers/links/edit.twig';
    protected $element_view_route = 'crawlers_links_edit';
    protected $module = "crawlers";
    private $crawler_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);
        $this->allowCrawlerOwnerOnly($crawler);
        
        $links = $this->mapper->getFromCrawler($crawler->id);
        return $this->ci->view->render($response, 'crawlers/links/index.twig', ['links' => $links, "crawler" => $crawler]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');
        $crawler_hash = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->getFromHash($crawler_hash);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $links = $this->mapper->getFromCrawler($crawler->id, 'position');

        $this->preEdit($entry_id, $request);

        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry, 'crawler' => $crawler, 'links' => $links]);
    }

    protected function afterSave($id, array $data, Request $request) {
        $entry = $this->mapper->get($id);
        $crawler_id = $entry->crawler;
        $crawler = $this->crawler_mapper->get($crawler_id);
        $this->index_params = ["crawler" => $crawler->getHash()];
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
        $user = $this->ci->get('helper')->getUser()->id;
        if ($crawler->user !== $user) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
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
