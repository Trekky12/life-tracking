<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\MainService;
use App\Application\Responder\JSONResponder;

class CSRFTokensAction {

    private $responder;
    private $service;

    public function __construct(JSONResponder $responder, MainService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $count = array_key_exists('count', $data) ? intval(filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT)) : 5;
        $payload = $this->service->getCSRFTokens($count);
        return $this->responder->respond($payload);
    }

}
