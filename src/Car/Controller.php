<?php

namespace App\Car;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Car\Car';
    protected $index_route = 'cars';
    protected $edit_template = 'cars/control/edit.twig';
    protected $element_view_route = 'cars_edit';
    protected $module = "cars";

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $cars = $this->mapper->getAll('name');
        return $this->twig->render($response, 'cars/control/index.twig', ['cars' => $cars]);
    }

}
