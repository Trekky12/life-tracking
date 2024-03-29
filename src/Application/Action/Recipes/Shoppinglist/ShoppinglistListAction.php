<?php

namespace App\Application\Action\Recipes\Shoppinglist;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Shoppinglist\ShoppinglistService;
use App\Application\Responder\HTMLTemplateResponder;

class ShoppinglistListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ShoppinglistService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('recipes/shoppinglists/index.twig'));
    }

}
