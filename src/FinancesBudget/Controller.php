<?php

namespace App\FinancesBudget;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

     private $cat_mapper;
    
    public function init() {
        $this->model = '\App\FinancesBudget\Budget';
        $this->index_route = 'finances_budgets';
        
        $this->mapper = new Mapper($this->ci);
        $this->cat_mapper = new \App\FinancesCategory\Mapper($this->ci);
    }
    
    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');

        return $this->ci->view->render($response, 'finances/budgets/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function index(Request $request, Response $response) {
        $budgets = $this->mapper->getAll('description');
        $categories = $this->cat_mapper->getAll();
        return $this->ci->view->render($response, 'finances/budgets/index.twig', ['budgets' => $budgets, 'categories' => $categories]);
    }

}
