<?php

namespace App\Application\Action\Timesheets\Sheet;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Timesheets\Sheet\SheetWriter;

class SheetSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, SheetWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["project" => $project_hash]);

        $view = $request->getQueryParam('view');
        $route_name = 'timesheets_sheets';
        if($view == 'calendar'){
            $route_name = 'timesheets_calendar';
        }
        return $this->responder->respond($entry->withRouteName($route_name)->withRouteParams(["project" => $project_hash]));
    }

}