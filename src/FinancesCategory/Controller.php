<?php

namespace App\FinancesCategory;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    
    public function init() {
        $this->model = '\App\FinancesCategory\Category';
        $this->index_route = 'finances_categories';
        $this->edit_template = 'finances/category/edit.twig';
        
        $this->mapper = new \App\Base\Mapper($this->ci, 'finances_categories', $this->model );
    }

    public function index(Request $request, Response $response) {
        $categories = $this->mapper->getAll('name');
        return $this->ci->view->render($response, 'finances/category/index.twig', ['categories' => $categories]);
    }

}
