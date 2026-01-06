<?php

namespace App\Application\Action\Timesheets\Reminder;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\Reminder\ReminderService;
use App\Application\Responder\HTMLTemplateResponder;

class ReminderListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, ReminderService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $project_hash = $request->getAttribute('project');
        $index = $this->service->index($project_hash);
        return $this->responder->respond($index->withTemplate('timesheets/reminder/index.twig'));
    }

}
