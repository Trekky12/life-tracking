<?php

namespace App\Application\Action\Recipes\Grocery;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Grocery\GroceryService;
use App\Application\Responder\JSONResultResponder;

class GrocerySelectionListAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, GroceryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $selected = $request->getQueryParam('selected');
        
        $payload = $this->service->getIngredients($selected);

        return $this->responder->respond($payload);
    }

}
