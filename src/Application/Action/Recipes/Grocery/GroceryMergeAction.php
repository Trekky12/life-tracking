<?php

namespace App\Application\Action\Recipes\Grocery;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Recipes\Grocery\GroceryService;

class GroceryMergeAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, GroceryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $entry = $this->service->mergeGroceries($data);
        return $this->responder->respond($entry->withRouteName('recipes_groceries'));
    }

}
