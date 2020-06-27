<?php

namespace App\Application\Action\Profile\ApplicationPasswords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\User\ApplicationPasswords\ApplicationPasswordWriter;

class ApplicationPasswordsSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, ApplicationPasswordWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('users_application_passwords'));
    }

}
