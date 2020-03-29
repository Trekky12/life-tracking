<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

abstract class Responder {

    protected $responseFactory;
    protected $translation;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        $this->responseFactory = $responseFactory;
        $this->translation = $translation;
    }

    protected function respond(Payload $payload): ResponseInterface {
        if ($payload->getStatus() == Payload::$NO_ACCESS) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }
        
        return $this->responseFactory->createResponse();
    }

}
