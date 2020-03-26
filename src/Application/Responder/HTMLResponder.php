<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

class HTMLResponder {

    private $responseFactory;
    private $twig;

    public function __construct(ResponseFactoryInterface $responseFactory, Twig $twig) {
        $this->responseFactory = $responseFactory;
        $this->twig = $twig;
    }

    public function respond($template, array $data = []): ResponseInterface {
        $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'text/html; charset=utf-8');
        return $this->twig->render($response, $template, $data);
    }

}
