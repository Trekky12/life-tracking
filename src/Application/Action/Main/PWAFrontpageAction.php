<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Home\HomeService;
use App\Application\Responder\RedirectResponder;

class PWAFrontpageAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, HomeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->getPWAStartPage();

        $data = $payload->getResult();
        return $this->responder->respond($data["url"], 302, false);
    }

}
