<?php

namespace App\User\Token;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class ControllerAdmin extends \App\Base\Controller {

    protected $index_route = 'login_tokens';

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $users = $this->user_mapper->getAll();
        return $this->twig->render($response, 'user/tokens.twig', ['list' => $list, 'users' => $users]);
    }

    public function deleteOld(Request $request, Response $response) {
        $this->mapper->deleteOldTokens();
        return $response->withRedirect($this->router->pathFor($this->index_route), 301);
    }

    public function deleteOldTokens() {
        return $this->mapper->deleteOldTokens();
    }

}
