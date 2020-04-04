<?php

namespace App\Application\Action\Finances\Budget;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Recurring\RecurringService;
use App\Application\Responder\JSONResultResponder;

class BudgetCategoryCostsAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, RecurringService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $category = $request->getQueryParam('category');
        $payload = $this->service->getCategoryCosts($category);
        return $this->responder->respond($payload);
    }

}
