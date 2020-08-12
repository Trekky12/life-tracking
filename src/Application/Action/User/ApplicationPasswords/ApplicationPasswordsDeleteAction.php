<?php

namespace App\Application\Action\User\ApplicationPasswords;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\User\ApplicationPasswords\ApplicationPasswordAdminRemover;
use App\Application\Responder\DeleteResponder;

class ApplicationPasswordsDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, ApplicationPasswordAdminRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        
        $user_id = $request->getAttribute('user');
        $user = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);
        
        $payload = $this->service->delete($id, $user);
        return $this->responder->respond($payload);
    }

}
