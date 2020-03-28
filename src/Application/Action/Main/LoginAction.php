<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\LoginService;
use App\Application\Responder\Main\LoginResponder;

class LoginAction {

    private $responder;
    private $service;

    public function __construct(LoginResponder $responder, LoginService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        $token = $this->service->login($data);

        return $this->responder->respond($token);
    }

}
