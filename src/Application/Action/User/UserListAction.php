<?php

namespace App\Application\Action\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\UserService;
use App\Application\Responder\HTMLResponder;

class UserListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, UserService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('user/index.twig'));
    }

}
