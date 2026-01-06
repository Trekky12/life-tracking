<?php

namespace App\Application\Action\Timesheets\Reminder;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Timesheets\Reminder\ReminderWriter;

class ReminderSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, ReminderWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute("project");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["project" => $project_hash]);
        return $this->responder->respond($entry->withRouteName('timesheets_reminders')->withRouteParams(["project" => $project_hash]));
    }
}
