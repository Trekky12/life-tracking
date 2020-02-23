<?php

namespace App\Finances\Category;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Finances\Category\Category';
    protected $index_route = 'finances_categories';
    protected $edit_template = 'finances/category/edit.twig';
    protected $element_view_route = 'finances_categories_edit';
    protected $module = "finances";

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $categories = $this->mapper->getAll('name');
        return $this->twig->render($response, 'finances/category/index.twig', ['categories' => $categories]);
    }

    protected function afterSave($id, array $data, Request $request) {
        $cat = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if ($cat->is_default == 1) {
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if (is_null($default)) {
            $this->mapper->set_default($id);
        }
    }

}
