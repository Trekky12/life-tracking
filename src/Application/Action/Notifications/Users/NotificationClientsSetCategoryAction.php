<?php

namespace App\Application\Action\Notifications\Users;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Users\NotificationUsersService;
use App\Application\Responder\JSONResultResponder;

class NotificationClientsSetCategoryAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, NotificationUsersService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->setCategoryForUser($data);

        return $this->responder->respond($payload);
    }

}
