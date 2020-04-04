<?php

namespace App\Application\Action\Notifications\Categories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Categories\NotificationCategoryService;
use App\Application\Responder\HTMLTemplateResponder;

class NotificationCategoryEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, NotificationCategoryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('notifications/categories/edit.twig'));
    }

}
