<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\MainService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Application\Responder\RedirectResponder;
use App\Application\Payload\Payload;

class FrontpageAction {

    private $responder;
    private $responder2;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, RedirectResponder $responder2, MainService $service) {
        $this->responder = $responder;
        $this->responder2 = $responder2;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $pwa = $request->getQueryParam('pwa', null);

        $user_start_page = $this->service->getUserStartPage();
        // is PWA? redirect to start page
        if (!is_null($pwa) && !is_null($user_start_page)) {
            return $this->responder2->respond($user_start_page, 301, false);
        }
        $payload = new Payload(Payload::$RESULT_HTML);
        return $this->responder->respond($payload->withTemplate('main/index.twig'));
    }

}
