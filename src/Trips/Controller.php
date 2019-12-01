<?php

namespace App\Trips;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Trips\Trip';
    protected $index_route = 'trips';
    protected $edit_template = 'trips/edit.twig';
    
    private $event_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->event_mapper = new Event\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $trips = $this->mapper->getUserItems('t.createdOn DESC, name');
        $dates = $this->event_mapper->getMinMaxEventsDates();
        return $this->ci->view->render($response, 'trips/index.twig', ['trips' => $trips, 'dates' => $dates]);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function afterSave($id, $data, Request $request) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->hash)) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
