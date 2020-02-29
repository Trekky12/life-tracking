<?php

namespace App\Finances\Assignment;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Finances\Assignment\Assignment';
    protected $index_route = 'finances_categories_assignment';
    protected $element_view_route = 'finances_categories_assignment_edit';
    protected $module = "finances";
    private $cat_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->cat_mapper = new \App\Finances\Category\Mapper($this->db, $this->translation, $user);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');

        return $this->twig->render($response, 'finances/assignment/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function index(Request $request, Response $response) {
        $assignments = $this->mapper->getAll('description');
        $categories = $this->cat_mapper->getAll();
        return $this->twig->render($response, 'finances/assignment/index.twig', ['assignments' => $assignments, 'categories' => $categories]);
    }

}
