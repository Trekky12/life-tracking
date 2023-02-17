<?php

namespace App\Application\Action\Recipes\Grocery;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Grocery\GroceryService;
use App\Application\Responder\HTMLTemplateResponder;

class GroceryListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, GroceryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('recipes/groceries/index.twig'));
    }

}
