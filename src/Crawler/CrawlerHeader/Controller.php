<?php

namespace App\Crawler\CrawlerHeader;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Crawler\CrawlerHeader\CrawlerHeader';
        $this->index_route = 'crawlers_headers';
        $this->edit_template = 'crawlers/headers/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $crawler_id = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->get($crawler_id);
        $headers = $this->mapper->getFromCrawler($crawler->id);
        return $this->ci->view->render($response, 'crawlers/headers/index.twig', ['headers' => $headers, "crawler" => $crawler]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');
        $crawler_id = $request->getAttribute('crawler');
        $crawler = $this->crawler_mapper->get($crawler_id);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $this->preEdit($entry_id);
        
        return $this->ci->view->render($response, $this->edit_template, ['entry' => $entry, 'crawler' => $crawler, "sortOptions" => $this->sortOptions()]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $crawler = $request->getAttribute('crawler');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        // Remove CSRF attributes
        if (array_key_exists('csrf_name', $data)) {
            unset($data["csrf_name"]);
        }
        if (array_key_exists('csrf_value', $data)) {
            unset($data["csrf_value"]);
        }

        $this->insertOrUpdate($id, $data);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["crawler" => $crawler]), 301);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id) {
        $this->allowOwnerOnly($id);
    }

    private function allowOwnerOnly($header_id) {
        $user = $this->ci->get('helper')->getUser()->id;
        if (!is_null($header_id)) {
            $header = $this->mapper->get($header_id);
            $crawler = $this->crawler_mapper->get($header->crawler);

            if ($crawler->user !== $user) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }
    
    private function sortOptions(){
        return [null => $this->ci->get('helper')->getTranslatedString('NO_INITIAL_SORTING'), "asc" => $this->ci->get('helper')->getTranslatedString('ASC'), "desc" => $this->ci->get('helper')->getTranslatedString('DESC')];
    }
    
    public function afterSave($id, $data) {
        $header = $this->mapper->get($id);
        
        // only one header can be initial sorted 
        // so remove the sort value on all others
        if(!is_null($header->sort)){
            $this->mapper->unset_sort($id);
        }
    }

}
