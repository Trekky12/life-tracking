<?php

namespace App\Application\Action\Notifications\Categories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Categories\NotificationCategoryRemover;
use App\Application\Responder\DeleteResponder;

class NotificationCategoryDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, NotificationCategoryRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id);
        return $this->responder->respond($payload);
    }

}
