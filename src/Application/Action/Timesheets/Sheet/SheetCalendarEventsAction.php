<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Application\Responder\JSONResultResponder;

class SheetCalendarEventsAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, SheetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');

        $requestData = $request->getQueryParams();

        $payload = $this->service->events($hash, $requestData);

        return $this->responder->respond($payload);
    }

}
