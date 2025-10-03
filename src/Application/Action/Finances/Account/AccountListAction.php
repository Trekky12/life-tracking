<?php

namespace App\Application\Action\Finances\Account;

use Slim\Http\ServerRequest as Request;
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
        $archive = $request->getParam('archive', 0);
        $index = $this->service->index($archive);
        return $this->responder->respond($index->withTemplate('finances/account/index.twig'));
    }

}
