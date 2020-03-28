<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Base\CurrentUser;
use App\Application\Responder\HTMLResponder;
use App\Application\Responder\RedirectResponder;

class LoginpageAction {

    private $responder;
    private $responder2;
    private $current_user;

    public function __construct(HTMLResponder $responder, RedirectResponder $responder2, CurrentUser $current_user) {
        $this->responder = $responder;
        $this->responder2 = $responder2;
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, Response $response): Response {
        $user = $this->current_user->getUser();
        // user is logged in, redirect to frontpage
        if (!is_null($user)) {
            return $this->responder2->respond('index');
        }
        return $this->responder->respond('main/login.twig');
    }

}
