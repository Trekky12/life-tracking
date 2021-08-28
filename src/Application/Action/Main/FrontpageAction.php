<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Home\HomeService;
use App\Application\Responder\HTMLTemplateResponder;

class FrontpageAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, HomeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->getUserStartPage();
        return $this->responder->respond($payload->withTemplate('home/index.twig'));
    }

}
