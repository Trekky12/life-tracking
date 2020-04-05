<?php

namespace App\Application\Action\Splitbill\Group;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Splitbill\Group\GroupWriter;

class GroupSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, GroupWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('splitbills'));
    }

}
