<?php

namespace App\Application\Action\Recipes\Shoppinglist;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\DeleteResponder;
use App\Domain\Recipes\Shoppinglist\ShoppinglistEntryService;

class ShoppinglistDeleteEntryAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, ShoppinglistEntryService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('shoppinglist');
        $id = $request->getAttribute('id');
        $payload = $this->service->deleteEntryFromShoppinglist($hash, $id);
        return $this->responder->respond($payload);
    }

}
