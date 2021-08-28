<?php

namespace App\Application\Action\Recipes\Mealplan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Recipes\Mealplan\MealplanService;

class MealplanMoveRecipeAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, MealplanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('mealplan');
        $data = $request->getParsedBody();
        $payload = $this->service->moveRecipeOnMealplan($hash, $data);
        return $this->responder->respond($payload);
    }

}
