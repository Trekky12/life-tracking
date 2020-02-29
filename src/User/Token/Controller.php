<?php

namespace App\User\Token;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $index_route = 'users_login_tokens';

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        // only tokens of current user
        $user = $this->user_helper->getUser();
        $this->mapper->setSelectFilterForUser($user);
        $list = $this->mapper->getAll();
        return $this->twig->render($response, 'user/tokens.twig', ['list' => $list]);
    }

    protected function preDelete($id, Request $request) {
        $user = $this->user_helper->getUser();
        $token = $this->mapper->get($id);

        if (intval($token->user) !== intval($user->id)) {
            throw new \Exception($this->translation->getTranslatedString("NO_ACCESS"));
        }
    }

}
