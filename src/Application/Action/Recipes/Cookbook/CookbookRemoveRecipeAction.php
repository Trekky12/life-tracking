<?php

namespace App\Application\Action\Recipes\Cookbook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\DeleteResponder;
use App\Domain\Recipes\Cookbook\CookbookService;

class CookbookRemoveRecipeAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, CookbookService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $cookbook = $request->getAttribute('cookbook');
        $recipe = $request->getQueryParam('recipe');
        $payload = $this->service->removeRecipeFromCookbook($cookbook, $recipe);

        return $this->responder->respond($payload->withRouteName('recipes_cookbooks_view')->withRouteParams(["cookbook" => $cookbook]));
    }

}
