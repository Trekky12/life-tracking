<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;

class RedirectResponder {

    private $responseFactory;
    private $router;

    public function __construct(ResponseFactoryInterface $responseFactory, RouteParser $router) {
        $this->responseFactory = $responseFactory;
        $this->router = $router;
    }

    public function respond($routeName, $status = 301, $resolve = true): ResponseInterface {
        $url = $resolve ? $this->router->urlFor($routeName) : $routeName;

        $response = $this->responseFactory->createResponse();
        return $response->withHeader('Location', $url)->withStatus($status);
    }

}
