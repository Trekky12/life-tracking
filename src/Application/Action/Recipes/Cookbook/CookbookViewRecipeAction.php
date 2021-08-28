<?php

namespace App\Application\Action\Recipes\Cookbook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Recipe\RecipeService;
use App\Application\Responder\HTMLTemplateResponder;

class CookbookViewRecipeAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, RecipeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $cookbook_hash = $request->getAttribute('cookbook');
        $recipe_hash = $request->getAttribute('recipe');
        $data = $this->service->view_single_in_cookbook($cookbook_hash, $recipe_hash);
        return $this->responder->respond($data->withTemplate('recipes/cookbooks/view-recipe-in-cookbook.twig'));
    }

}
