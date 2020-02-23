<?php

namespace App\User\MobileFavorites;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\User\MobileFavorites\MobileFavorite';
    protected $index_route = 'users_mobile_favorites';
    protected $edit_template = 'profile/mobile_favorites/edit.twig';
    protected $element_view_route = 'users_mobile_favorites_edit';

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll('position');
        return $this->twig->render($response, 'profile/mobile_favorites/index.twig', ['list' => $list]);
    }

}
