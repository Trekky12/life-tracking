<?php

namespace App\User\MobileFavorites;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\User\MobileFavorites\MobileFavorite';
        $this->index_route = 'users_mobile_favorites';
        $this->edit_template = 'profile/mobile_favorites/edit.twig';

        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll('position');
        return $this->ci->view->render($response, 'profile/mobile_favorites/index.twig', ['list' => $list]);
    }

}
