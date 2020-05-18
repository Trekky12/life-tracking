<?php

namespace App\Application\Action\Finances\Recurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\RedirectResponder;
use App\Domain\Finances\Recurring\RecurringFinanceEntryCreator;

class RecurringTriggerAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, RecurringFinanceEntryCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $entry = $this->service->createEntry($id);
        
        return $this->responder->respond('finances', 301, true);
    }

}
