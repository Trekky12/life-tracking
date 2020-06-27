<?php

namespace App\Application\Action\User\ApplicationPasswords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\ApplicationPasswords\ApplicationPasswordAdminService;
use App\Application\Responder\HTMLTemplateResponder;

class ApplicationPasswordsListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ApplicationPasswordAdminService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $user_id = $request->getAttribute('user');
        $index = $this->service->index($user_id);
        return $this->responder->respond($index->withTemplate('profile/application_passwords/index.twig'));
    }

}
