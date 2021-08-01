<?php

namespace App\Application\Action\Recipes\Mealplan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Mealplan\MealplanService;
use App\Application\Responder\HTMLTemplateResponder;

class MealplanListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MealplanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('recipes/mealplans/index.twig'));
    }

}
