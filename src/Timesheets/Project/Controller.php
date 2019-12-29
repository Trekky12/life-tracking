<?php

namespace App\Timesheets\Project;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Timesheets\Project\Project';
    protected $index_route = 'timesheets_projects';
    protected $edit_template = 'timesheets/projects/edit.twig';
    protected $element_view_route = 'timesheets_projects_edit';
    protected $module = "timesheets";

    public function init() {
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $projects = $this->mapper->getUserItems('t.createdOn DESC, name');
        return $this->ci->view->render($response, 'timesheets/projects/index.twig', ['projects' => $projects]);
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
