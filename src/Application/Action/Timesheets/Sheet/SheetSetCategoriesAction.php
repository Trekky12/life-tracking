<?php

namespace App\Application\Action\Timesheets\Sheet;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Timesheets\Sheet\SheetService;

class SheetSetCategoriesAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, SheetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');
        $data = $request->getParsedBody();
        $payload = $this->service->setCategories($hash, $data);
        return $this->responder->respond($payload);
    }

}
