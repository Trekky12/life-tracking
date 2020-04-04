<?php

namespace App\Application\Action\Finances\Paymethod;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Responder\HTMLTemplateResponder;

class PaymethodEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, PaymethodService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($entry_id);
        return $this->responder->respond($data->withTemplate('finances/paymethod/edit.twig'));
    }

}
