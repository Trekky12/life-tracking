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
        return $this->ci->view->render($response, 'notifications/categories/index.twig', ['categories' => $categories]);
    }



}
