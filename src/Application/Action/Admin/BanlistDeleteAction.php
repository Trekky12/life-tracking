<?php

namespace App\Application\Action\Admin;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Admin\Banlist\BanRemover;
use App\Application\Responder\DeleteResponder;

class BanlistDeleteAction {

    private $responder;
    private $service;

    public function __construct(DeleteResponder $responder, BanRemover $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $ip = $request->getAttribute('ip');
        $payload = $this->service->delete($ip);
        return $this->responder->respond($payload);
    }

}
