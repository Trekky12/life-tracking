<?php

namespace App\Application\Action\Recipes\Shoppinglist;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Shoppinglist\ShoppinglistService;
use App\Application\Responder\JSONResultResponder;

class ShoppinglistEntrySetStateAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, ShoppinglistService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('shoppinglist');
        $data = $request->getParsedBody();
        $payload = $this->service->setState($hash, $data);
        return $this->responder->respond($payload);
    }

}
