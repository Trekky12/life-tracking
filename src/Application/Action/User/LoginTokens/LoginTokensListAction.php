<?php

namespace App\Application\Action\User\LoginTokens;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Token\TokenAdminService;
use App\Application\Responder\HTMLTemplateResponder;

class LoginTokensListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TokenAdminService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->index();
        return $this->responder->respond($payload->withTemplate('user/tokens.twig'));
    }

}
