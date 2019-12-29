<?php

namespace App\Finances\Paymethod;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Finances\Paymethod\Paymethod';
    protected $index_route = 'finances_paymethod';
    protected $edit_template = 'finances/paymethod/edit.twig';
    protected $element_view_route = 'finances_paymethod_edit';
    protected $module = "finances";

    public function init() {
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $paymethods = $this->mapper->getAll('name');
        return $this->ci->view->render($response, 'finances/paymethod/index.twig', ['paymethods' => $paymethods]);
    }

    protected function afterSave($id, array $data, Request $request) {
        $method = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if($method->is_default == 1){
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if(is_null($default)){
            $this->mapper->set_default($id);
        }
    }

}
