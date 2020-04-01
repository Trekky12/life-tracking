<?php

namespace App\Application\Action\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\UserService;
use App\Application\Responder\User\TestMailResponder;

class TestMailAction {

    private $responder;
    private $service;

    public function __construct(TestMailResponder $responder, UserService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $user_id = $request->getAttribute('user');
        $payload = $this->service->testMail($user_id);
        return $this->responder->respond($payload);
    }

}
