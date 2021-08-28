<?php

namespace App\Application\Action\Recipes\Cookbook;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Recipes\Cookbook\CookbookService;

class CookbookAddRecipeAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, CookbookService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $entry = $this->service->addRecipeToCookbook($data);
        return $this->responder->respond($entry->withRouteName('recipes'));
    }

}
