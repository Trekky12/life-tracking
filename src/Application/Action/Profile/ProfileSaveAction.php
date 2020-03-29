<?php

namespace App\Application\Action\Profile;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Profile\ProfileService;
use App\Application\Responder\SaveResponder;

class ProfileSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, ProfileService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->updateUser($data);

        return $this->responder->respond($payload->withRouteName('users_profile_edit'));
    }

}
