<?php

namespace App\Application\Action\User\LoginTokens;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Token\TokenService;
use App\Application\Responder\RedirectResponder;

class LoginTokensDeleteOldAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, TokenService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->deleteOldTokens();
        return $this->responder->respond('login_tokens', 301, true);
    }

}
