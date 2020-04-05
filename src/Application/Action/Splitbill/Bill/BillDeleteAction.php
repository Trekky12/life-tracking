<?php

namespace App\Application\Action\Splitbill\Bill;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Splitbill\Bill\BillRemover;
use App\Application\Responder\DeleteResponder;

class BillDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, BillRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $group_hash = $request->getAttribute("group");
        $id = $request->getAttribute('id');
        $payload = $this->service->delete($id, ["group" => $group_hash]);
        return $this->responder->respond($payload);
    }

}
