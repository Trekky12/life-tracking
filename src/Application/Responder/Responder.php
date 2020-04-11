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

    public function respond(Payload $payload): ResponseInterface {
        return $this->responseFactory->createResponse();
    }

}
