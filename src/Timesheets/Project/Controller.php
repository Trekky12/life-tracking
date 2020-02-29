<?php

namespace App\Timesheets\Project;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Timesheets\Project\Project';
    protected $index_route = 'timesheets';
    protected $edit_template = 'timesheets/projects/edit.twig';
    protected $element_view_route = 'timesheets_projects_edit';
    protected $module = "timesheets";

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $projects = $this->mapper->getUserItems('t.createdOn DESC, name');
        return $this->twig->render($response, 'timesheets/projects/index.twig', ['projects' => $projects]);
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
