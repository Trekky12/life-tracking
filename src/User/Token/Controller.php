<?php

namespace App\User\Token;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->index_route = 'users_login_tokens';
        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        // only tokens of current user
        $this->mapper->setFilterByUser(true);
        $list = $this->mapper->getAll();
        return $this->ci->view->render($response, 'user/tokens.twig', ['list' => $list]);
    }

    protected function preDelete($id, Request $request) {
        $user = $this->ci->get('helper')->getUser();
        $token = $this->mapper->get($id);

        if (intval($token->user) !== intval($user->id)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString("NO_ACCESS"));
        }
    }

}
