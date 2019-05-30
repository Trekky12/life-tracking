<?php

namespace App\Trips;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Trips\Trip';
        $this->index_route = 'trips';
        $this->edit_template = 'trips/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $trips = $this->mapper->getUserItems('t.createdOn DESC, name');
        return $this->ci->view->render($response, 'trips/index.twig', ['trips' => $trips]);
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

    protected function afterSave($id, $data) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->hash)) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
