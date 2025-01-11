<?php

namespace App\Application\Action\Main;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Admin\Setup\SetupService;
use App\Application\Responder\HTMLTemplateResponder;

class SetupViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, SetupService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $view = $this->service->getSetupPage();
        return $this->responder->respond($view->withTemplate('main/setup.twig'));
    }

}
