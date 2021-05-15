<?php

namespace App\Application\Action\Recipes\Recipe;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Recipes\Recipe\RecipeWriter;

class RecipeSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, RecipeWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $entry = $this->service->save($id, $data, ["files" => $files]);
        
        return $this->responder->respond($entry->withRouteName('recipes_recipe_view')->withRouteParams(["recipe" => $entry->getAdditionalData()["hash"]]));
    }

}
