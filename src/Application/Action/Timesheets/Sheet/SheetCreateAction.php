<?php

namespace App\Application\Action\Timesheets\Sheet;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\RedirectResponder;
use App\Domain\Timesheets\Sheet\SheetCreator;

class SheetCreateAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, SheetCreator $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $payload = $this->service->createEntry($project_hash);

        $entry = $payload->getResult();

        return $this->responder->respond('timesheets_sheets_notice_edit', 301, true, ["project" => $project_hash, "sheet" => $entry->id]);
    }
}
