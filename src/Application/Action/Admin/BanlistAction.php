<?php

namespace App\Application\Action\Admin;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Admin\Banlist\BanlistService;
use App\Application\Responder\HTMLResponder;

class BanlistAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, BanlistService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('main/banlist.twig'));
    }

}
