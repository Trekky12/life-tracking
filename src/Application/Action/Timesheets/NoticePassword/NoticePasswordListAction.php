<?php

namespace App\Application\Action\Timesheets\NoticePassword;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Timesheets\NoticePassword\NoticePasswordService;
use App\Application\Responder\HTMLTemplateResponder;

class NoticePasswordListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, NoticePasswordService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');
        $index = $this->service->index($hash);
        return $this->responder->respond($index->withTemplate('timesheets/noticepassword/edit.twig'));
    }

}
