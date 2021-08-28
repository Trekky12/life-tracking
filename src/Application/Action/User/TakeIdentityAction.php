<?php

namespace App\Application\Action\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Token\TokenService;
use App\Application\Responder\RedirectResponder;

class TakeIdentityAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, TokenService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $user_id = $request->getAttribute('user');
        $payload = $this->service->takeIdentity($user_id);
        
        return $this->responder->respond('index', 301, true);
    }

}
