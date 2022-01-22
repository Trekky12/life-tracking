<?php

namespace App\Application\Action\Finances\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Finances\Account\AccountWriter;

class AccountSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, AccountWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('finances_account'));
    }

}
