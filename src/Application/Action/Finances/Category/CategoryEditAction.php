<?php

namespace App\Application\Action\Finances\Category;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Category\CategoryService;
use App\Application\Responder\HTMLResponder;

class CategoryEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, CategoryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond('finances/category/edit.twig', $data);
    }

}
