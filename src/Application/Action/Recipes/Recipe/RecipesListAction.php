<?php

namespace App\Application\Action\Recipes\Recipe;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Recipe\RecipeService;
use App\Application\Responder\JSONHTMLTemplateResponder;

class RecipesListAction {

    private $responder;
    private $service;

    public function __construct(JSONHTMLTemplateResponder $responder, RecipeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getQueryParams();
        $payload = $this->service->getRecipes($data);

        return $this->responder->respond($payload->withTemplate('recipes/recipe/recipes-list.twig'));
    }

}
