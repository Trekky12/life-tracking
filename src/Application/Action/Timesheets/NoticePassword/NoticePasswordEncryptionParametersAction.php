<?php

namespace App\Application\Action\Timesheets\NoticePassword;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\JSONResultResponder;
use App\Domain\Timesheets\NoticePassword\NoticePasswordService;

class NoticePasswordEncryptionParametersAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, NoticePasswordService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('project');
        $data = $request->getParsedBody();
        $entry = $this->service->getEncryptionParameters($hash, $data);
        return $this->responder->respond($entry);
    }

}
