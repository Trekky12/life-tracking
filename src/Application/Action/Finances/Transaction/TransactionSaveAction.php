<?php

namespace App\Application\Action\Finances\Transaction;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Finances\Transaction\TransactionWriter;

class TransactionSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, TransactionWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $account_hash = $request->getQueryParam('account');
        $payload = $this->service->save($id, $data, ["account" => $account_hash]);

        $additionalData = $payload->getAdditionalData();
        if(array_key_exists("account", $additionalData) && !empty($additionalData["account"])){
            return $this->responder->respond($payload->withRouteName('finances_transaction')->withRouteParams(["account" => $additionalData["account"]->getHash()]));
        }

        return $this->responder->respond($payload->withRouteName('finances_account'));
    }

}
