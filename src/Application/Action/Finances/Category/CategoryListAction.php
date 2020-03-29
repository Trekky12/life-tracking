<?php

namespace App\Application\Action\Finances\Category;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Category\CategoryService;
use App\Application\Responder\HTMLResponder;

class CategoryListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, CategoryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $categories = $this->service->index();
        return $this->responder->respond($categories->withTemplate('finances/category/index.twig'));
    }

}
