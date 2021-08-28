<?php

namespace App\Application\Action\Recipes\Recipe;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Recipe\RecipeService;
use App\Application\Responder\HTMLTemplateResponder;

class RecipeAddToCookbookAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, RecipeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('recipe');
        $data = $this->service->add_to_cookbook($hash);
        return $this->responder->respond($data->withTemplate('recipes/recipe/add-to-cookbook.twig'));
    }

}
