<?php

namespace App\Application\Action\Profile;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\Profile\ProfileService;
use App\Application\Responder\HTMLTemplateResponder;

class ProfileEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ProfileService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->editProfilePage();

        return $this->responder->respond($payload->withTemplate('profile/edit.twig'));
    }

}
