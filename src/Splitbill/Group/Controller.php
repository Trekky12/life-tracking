<?php

namespace App\Splitbill\Group;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Splitbill\Group\Group';
        $this->index_route = 'splitbill_groups';
        $this->edit_template = 'splitbills/groups/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->bill_mapper = new \App\Splitbill\Bill\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $groups = $this->mapper->getUserItems('t.createdOn DESC, name');
        $balances = $this->bill_mapper->getBalances();
        return $this->ci->view->render($response, 'splitbills/groups/index.twig', ['groups' => $groups, 'balances' => $balances]);
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
