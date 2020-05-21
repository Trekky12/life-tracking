<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Home\HomeService;
use App\Application\Responder\Home\FrontpageResponder;

class FrontpageAction {

    private $responder;
    private $service;

    public function __construct(FrontpageResponder $responder, HomeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $pwa = $request->getQueryParam('pwa', null);

        $payload = $this->service->getUserStartPage($pwa);
        return $this->responder->respond($payload->withTemplate('home/index.twig'));
    }

}
