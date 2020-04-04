<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class JSONResultResponder extends JSONResponder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        parent::__construct($responseFactory, $translation);
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $data = $payload->getResult();
        $json = json_encode($data);
        if ($json === false) {
            throw new \RuntimeException('Malformed UTF-8 characters, possibly incorrectly encoded.');
        }

        $response->getBody()->write($json);

        return $response;
    }

}
