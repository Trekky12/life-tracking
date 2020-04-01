<?php

namespace App\Application\Action\Notifications\Categories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Notifications\Categories\NotificationCategoryWriter;

class NotificationCategorySaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, NotificationCategoryWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('notifications_categories'));
    }

}
