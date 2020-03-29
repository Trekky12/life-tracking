<?php

namespace App\Application\Action\Profile;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Profile\ProfileService;
use App\Application\Responder\Profile\ChangePasswordResponder;

class ChangePasswordAction {

    private $responder;
    private $service;

    public function __construct(ChangePasswordResponder $responder, ProfileService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->changePassword($data);

        return $this->responder->respond($payload);
    }

}
