<?php

namespace App\Application\Action\User;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\JSONResultResponder;
use App\Domain\User\UserService;

class UserSearchAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, UserService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getQueryParams();
        $payload = $this->service->getData($data);
        return $this->responder->respond($payload);
    }

}
