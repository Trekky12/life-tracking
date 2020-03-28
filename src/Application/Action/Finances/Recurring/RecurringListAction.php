<?php

namespace App\Application\Action\Finances\Recurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Recurring\RecurringService;
use App\Application\Responder\HTMLResponder;

class RecurringListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, RecurringService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond('finances/recurring/index.twig', $index);
    }

}
