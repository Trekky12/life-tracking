<?php

namespace App\Application\Action\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\UserService;
use App\Application\Responder\HTMLResponder;

class UserEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, UserService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('user/edit.twig'));
    }

}
