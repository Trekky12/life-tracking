<?php

namespace App\Application\Action\Recipes\Shoppinglist;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Recipes\Shoppinglist\ShoppinglistEntryService;

class ShoppinglistAddEntryAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, ShoppinglistEntryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('shoppinglist');
        $data = $request->getParsedBody();
        $payload = $this->service->addEntryToShoppinglist($hash, $data);
        return $this->responder->respond($payload);
    }

}
