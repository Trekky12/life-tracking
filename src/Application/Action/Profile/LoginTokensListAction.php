<?php

namespace App\Application\Action\Profile;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Token\TokenService;
use App\Application\Responder\HTMLResponder;

class LoginTokensListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, TokenService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->index();

        return $this->responder->respond($payload->withTemplate('user/tokens.twig'));
    }

}
