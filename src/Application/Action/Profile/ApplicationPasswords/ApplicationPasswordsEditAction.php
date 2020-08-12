<?php

namespace App\Application\Action\Profile\ApplicationPasswords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\ApplicationPasswords\ApplicationPasswordService;
use App\Application\Responder\HTMLTemplateResponder;

class ApplicationPasswordsEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ApplicationPasswordService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('profile/application_passwords/edit.twig'));
    }

}
