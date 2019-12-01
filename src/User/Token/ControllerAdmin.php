<?php

namespace App\User\Token;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ControllerAdmin extends \App\Base\Controller {

    protected $index_route = 'login_tokens';

    public function init() {
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $users = $this->user_mapper->getAll();
        return $this->ci->view->render($response, 'user/tokens.twig', ['list' => $list, 'users' => $users]);
    }

    public function deleteOld(Request $request, Response $response) {
        $this->mapper->deleteOldTokens();
        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
    }

    public function deleteOldTokens() {
        return $this->mapper->deleteOldTokens();
    }

}
