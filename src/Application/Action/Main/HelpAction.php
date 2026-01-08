<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\HelpService;
use App\Application\Responder\HTMLTemplateResponder;

class HelpAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, HelpService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $payload = $this->service->getHelpPage();

        $additionalData = $payload->getAdditionalData();
        if (array_key_exists("template", $additionalData)) {
            return $this->responder->respond($payload->withTemplate('help/' . $additionalData['template'] . '.twig'));
        }
        return $this->responder->respond($payload->withTemplate('/help/en.twig'));
    }
}
