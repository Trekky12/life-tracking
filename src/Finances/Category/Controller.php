<?php

namespace App\Finances\Category;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    
    public function init() {
        $this->model = '\App\Finances\Category\Category';
        $this->index_route = 'finances_categories';
        $this->edit_template = 'finances/category/edit.twig';
        
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $categories = $this->mapper->getAll('name');
        return $this->ci->view->render($response, 'finances/category/index.twig', ['categories' => $categories]);
    }

    
    public function afterSave($id, $data) {
        $cat = $this->mapper->get($id);
        
        // Set all other non-default, since there can only be one default category
        if($cat->is_default == 1){
            $this->mapper->unset_default($id);
        }
        
        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if(is_null($default)){
            $this->mapper->set_default($id);
        }
    }

}