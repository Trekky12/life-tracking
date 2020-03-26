<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;

class JSONResponder {

    private $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory) {
        $this->responseFactory = $responseFactory;
    }

    public function respond(Payload $payload): ResponseInterface {
        $data = $payload->getResult();
        $json = json_encode($data);
        if ($json === false) {
            throw new RuntimeException('Malformed UTF-8 characters, possibly incorrectly encoded.');
        }

        $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'application/json');

        $response->getBody()->write($json);

        return $response;
    }

}
