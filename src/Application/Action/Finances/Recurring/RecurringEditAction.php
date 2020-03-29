<?php

namespace App\Application\Action\Finances\Recurring;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Recurring\RecurringService;
use App\Application\Responder\HTMLResponder;

class RecurringEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, RecurringService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('finances/recurring/edit.twig'));
    }

}
