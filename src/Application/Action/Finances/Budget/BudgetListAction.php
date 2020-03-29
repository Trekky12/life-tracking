<?php

namespace App\Application\Action\Finances\Budget;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Budget\BudgetService;
use App\Application\Responder\HTMLResponder;

class BudgetListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, BudgetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('finances/budget/index.twig'));
    }

}
