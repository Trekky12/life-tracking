<?php

namespace App\Application\Action\Timesheets\Sheet;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class SheetEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SheetService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $entry_id = $request->getAttribute('id');
        $requestData = $request->getQueryParams();     
        $data = $this->service->edit($project_hash, $entry_id, $requestData);
        return $this->responder->respond($data->withTemplate('timesheets/sheets/edit.twig'));
    }

}
