<?php

namespace App\Trips;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Trips\Trip';
    protected $index_route = 'trips';
    protected $edit_template = 'trips/edit.twig';
    protected $element_view_route = 'trips_edit';
    protected $module = "trips";
    private $event_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->event_mapper = new Event\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $trips = $this->mapper->getUserItems('t.createdOn DESC, name');
        $dates = $this->event_mapper->getMinMaxEventsDates();
        return $this->twig->render($response, 'trips/index.twig', ['trips' => $trips, 'dates' => $dates]);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function afterSave($id, array $data, Request $request) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->getHash())) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

}
