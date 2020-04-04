<?php

namespace App\Application\Action\Finances\Budget;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Budget\BudgetService;
use App\Application\Responder\HTMLTemplateResponder;

class BudgetEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, BudgetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $this->service->edit();
        return $this->responder->respond($data->withTemplate('finances/budget/edit.twig'));
    }

}
