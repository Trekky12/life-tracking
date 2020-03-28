<?php

namespace App\Application\Responder\Main;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use Dflydev\FigCookies\FigResponseCookies;

class LogoutResponder {

    private $responseFactory;
    private $router;

    public function __construct(ResponseFactoryInterface $responseFactory, RouteParser $router) {
        $this->responseFactory = $responseFactory;
        $this->router = $router;
    }

    public function respond($routeName): ResponseInterface {

        $response = $this->responseFactory->createResponse();
        $response = FigResponseCookies::expire($response, 'token');

        return $response->withHeader('Location', $this->router->urlFor($routeName))->withStatus(302);
    }

}
