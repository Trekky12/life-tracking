<?php

namespace App\Application\Action\Board\Board;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Board\BoardService;
use App\Application\Responder\HTMLTemplateResponder;
use Dflydev\FigCookies\FigRequestCookies;

class BoardViewAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, BoardService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('hash');
        $sidebar_mobilevisible = FigRequestCookies::get($request, 'sidebar_mobilevisible');
        $sidebar_desktophidden = FigRequestCookies::get($request, 'sidebar_desktophidden');

        $sidebar = [
            "mobilevisible" => $sidebar_mobilevisible->getValue(),
            "desktophidden" => $sidebar_desktophidden->getValue(),
        ];
        $index = $this->service->view($hash, $sidebar);

        return $this->responder->respond($index->withTemplate('boards/view.twig'));
    }

}
