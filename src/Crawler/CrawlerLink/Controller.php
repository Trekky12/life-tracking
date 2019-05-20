<?php

namespace App\Crawler\CrawlerLink;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Crawler\CrawlerLink\CrawlerLink';
        $this->index_route = 'crawlers_links';
        $this->edit_template = 'crawlers/links/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $crawler_id = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->get($crawler_id);
        $links = $this->mapper->getFromCrawler($crawler->id);
        return $this->ci->view->render($response, 'crawlers/links/index.twig', ['links' => $links, "crawler" => $crawler]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');
        $crawler_id = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->get($crawler_id);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $links = $this->mapper->getAll('position');

        $this->preEdit($entry_id);

        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry, 'crawler' => $crawler, 'links' => $links]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $crawler = $request->getAttribute('crawler');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["crawler" => $crawler]), 301);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preEdit($id) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preDelete($id) {
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

}
