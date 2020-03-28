<?php

namespace App\Application\Action\Finances\Category;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Finances\Category\CategoryWriter;

class CategorySaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, CategoryWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond('finances_categories', $entry);
    }

}
