<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\LoginService;
use App\Application\Responder\Main\LogoutResponder;
use Dflydev\FigCookies\FigRequestCookies;

class LogoutAction {

    private $responder;
    private $service;

    public function __construct(LogoutResponder $responder, LoginService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $token = FigRequestCookies::get($request, 'token');
        $this->service->removeToken($token->getValue());
        return $this->responder->respond('login');
    }

}
