<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\MainService;
use App\Application\Responder\HTMLResponder;
use App\Application\Responder\RedirectResponder;

class FrontpageAction {

    private $responder;
    private $responder2;
    private $service;

    public function __construct(HTMLResponder $responder, RedirectResponder $responder2, MainService $service) {
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
        return $this->responder->respond('main/index.twig');
    }

}
