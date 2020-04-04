<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use App\Application\Error\JSONException;

abstract class JSONResponder extends Responder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        parent::__construct($responseFactory, $translation);
    }

    public function respond(Payload $payload): ResponseInterface {
        if ($payload->getStatus() == Payload::$NO_ACCESS) {
            throw new JSONException($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }
        return parent::respond($payload)->withHeader('Content-Type', 'application/json');
    }

}
