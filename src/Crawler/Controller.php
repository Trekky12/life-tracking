<?php

namespace App\Crawler;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Crawler\Crawler';
        $this->index_route = 'crawlers';
        $this->edit_template = 'crawlers/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $crawlers = $this->mapper->getVisibleCrawlers('name');
        return $this->ci->view->render($response, 'crawlers/index.twig', ['crawlers' => $crawlers]);
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

    private function allowOwnerOnly($crawler_id) {
        $user = $this->ci->get('helper')->getUser()->id;
        if (!is_null($crawler_id)) {
            $crawler = $this->mapper->get($crawler_id);

            if ($crawler->user !== $user) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    protected function afterSave($id, $data) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->hash)) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
