<?php

namespace App\Application\Action\Notifications\Categories;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Notifications\Categories\NotificationCategoryService;
use App\Application\Responder\HTMLResponder;

class NotificationCategoryListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, NotificationCategoryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('notifications/categories/index.twig'));
    }

}
