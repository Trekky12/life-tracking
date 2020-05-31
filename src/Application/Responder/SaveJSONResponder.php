<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class SaveJSONResponder extends JSONResponder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        parent::__construct($responseFactory, $translation);
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $data = ["status" => "success"];
        switch ($payload->getStatus()) {
            case Payload::$STATUS_PARSING_ERRORS:
                $data = ["status" => "error"];
                break;
            case Payload::$STATUS_ERROR:
                $data = ["status" => "error", "error" => $payload->getResult()];
                break;
        }

        $json = json_encode($data);
        $response->getBody()->write($json);

        return $response;
    }

}
