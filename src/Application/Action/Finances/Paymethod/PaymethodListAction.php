<?php

namespace App\Application\Action\Finances\Paymethod;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Responder\HTMLResponder;

class PaymethodListAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, PaymethodService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('finances/paymethod/index.twig'));
    }

}
