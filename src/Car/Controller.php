<?php

namespace App\Car;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Car\Car';
    protected $index_route = 'cars';
    protected $edit_template = 'cars/control/edit.twig';

    public function init() {
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $cars = $this->mapper->getAll('name');
        return $this->ci->view->render($response, 'cars/control/index.twig', ['cars' => $cars]);
    }

}
