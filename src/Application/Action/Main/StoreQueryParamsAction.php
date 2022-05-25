<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\JSONResultResponder;
use App\Application\Payload\Payload;

class StoreQueryParamsAction
{

    private $responder;

    public function __construct(JSONResultResponder $responder)
    {
        $this->responder = $responder;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $payload = new Payload(Payload::$RESULT_JSON, ["status" => "done"]);

        return $this->responder->respond($payload);
    }
}
