<?php

namespace App\Application\Action\Recipes\Mealplan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\DeleteResponder;
use App\Domain\Recipes\Mealplan\MealplanService;

class MealplanRemoveRecipeAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, MealplanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $mealplan = $request->getAttribute('mealplan');
        $data = $request->getParsedBody();
        $payload = $this->service->removeRecipeFromMealplan($mealplan, $data);

        return $this->responder->respond($payload);
    }

}
