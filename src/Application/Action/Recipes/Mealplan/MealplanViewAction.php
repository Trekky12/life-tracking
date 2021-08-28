<?php

namespace App\Application\Action\Recipes\Mealplan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Recipes\Mealplan\MealplanService;
use App\Application\Responder\HTMLTemplateResponder;

use App\Domain\Main\Utility\DateUtility;

class MealplanViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, MealplanService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('mealplan');
        
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData, null, null);
        
        $index = $this->service->view($hash, $from, $to);
        return $this->responder->respond($index->withTemplate('recipes/mealplans/view.twig'));
    }

}
