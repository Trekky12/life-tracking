<?php

namespace App\Application\Action\Recipes\Ingredient;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Ingredient\IngredientService;
use App\Application\Responder\HTMLTemplateResponder;

class IngredientListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, IngredientService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('recipes/ingredient/index.twig'));
    }

}
