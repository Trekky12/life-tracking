<?php

namespace App\FinancesCategoryAssignment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

     private $cat_mapper;
    
    public function init() {
        $this->model = '\App\FinancesCategoryAssignment\Assignment';
        $this->index_route = 'finances_categories_assignment';
        
        $this->mapper = new \App\FinancesCategoryAssignment\Mapper($this->ci);
        $this->cat_mapper = new \App\FinancesCategory\Mapper($this->ci);
    }
    
    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');

        return $this->ci->view->render($response, 'finances/assignment/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function index(Request $request, Response $response) {
        $assignments = $this->mapper->getAll('description');
        $categories = $this->cat_mapper->getAll();
        return $this->ci->view->render($response, 'finances/assignment/index.twig', ['assignments' => $assignments, 'categories' => $categories]);
    }

}
