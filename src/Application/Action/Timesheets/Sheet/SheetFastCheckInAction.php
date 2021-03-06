<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetFastWriter;
use App\Application\Responder\JSONResultResponder;

class SheetFastCheckInAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, SheetFastWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');
        $requestData = $request->getParsedBody();
        $payload = $this->service->fastCheckIn($hash, $requestData);
        return $this->responder->respond($payload);
        
    }

}
