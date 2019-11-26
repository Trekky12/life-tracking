<?php

namespace App\Notifications\Categories;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    
    public function init() {
        $this->model = '\App\Notifications\Categories\Category';
        $this->index_route = 'notifications_categories';
        $this->edit_template = 'notifications/categories/edit.twig';
        
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $categories = $this->mapper->getAll('name');
        $categories_filtered = array_filter($categories, function($cat){
            return !$cat->isInternal();
        });
        return $this->ci->view->render($response, 'notifications/categories/index.twig', ['categories' => $categories_filtered]);
    }
    
    protected function preEdit($id, Request $request) {
        $this->checkAccess($id);
    }
    
    protected function preSave($id, array &$data, Request $request) {
        $this->checkAccess($id);
    }

    private function checkAccess($id){
        if (!is_null($id)) {
            $cat = $this->mapper->get($id);
            if ($cat->isInternal()) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }


}
