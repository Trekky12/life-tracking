<?php

namespace App\Application\Action\Finances\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Finances\Account\AccountService;
use App\Application\Responder\HTMLTemplateResponder;

class AccountListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, AccountService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $index = $this->service->index();
        return $this->responder->respond($index->withTemplate('finances/account/index.twig'));
    }

}
