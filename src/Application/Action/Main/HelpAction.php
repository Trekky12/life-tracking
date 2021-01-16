<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\HelpService;
use App\Application\Responder\Home\FrontpageResponder;

class HelpAction {

    private $responder;
    private $service;

    public function __construct(FrontpageResponder $responder, HelpService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->getHelpPage();
        return $this->responder->respond($payload->withTemplate('main/help.twig'));
    }

}
