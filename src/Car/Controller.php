<?php

namespace App\Car;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    
    public function init() {
        $this->model = '\App\Car\Car';
        $this->index_route = 'cars';
        $this->edit_template = 'cars/control/edit.twig';
        
        $this->mapper = new \App\Car\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $cars = $this->mapper->getAll('name');
        return $this->ci->view->render($response, 'cars/control/index.twig', ['cars' => $cars]);
    }

}
