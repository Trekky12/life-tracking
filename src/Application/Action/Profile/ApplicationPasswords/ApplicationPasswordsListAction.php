<?php

namespace App\Application\Action\Profile\ApplicationPasswords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\ApplicationPasswords\ApplicationPasswordService;
use App\Application\Responder\HTMLTemplateResponder;

class ApplicationPasswordsListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ApplicationPasswordService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('profile/application_passwords/index.twig'));
    }

}
