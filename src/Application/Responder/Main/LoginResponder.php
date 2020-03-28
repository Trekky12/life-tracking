<?php

namespace App\Application\Responder\Main;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use \Slim\Flash\Messages as Flash;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;

class LoginResponder {

    private $responseFactory;
    private $router;
    private $flash;
    private $translation;

    public function __construct(ResponseFactoryInterface $responseFactory, RouteParser $router, Flash $flash, Translator $translation) {
        $this->responseFactory = $responseFactory;
        $this->router = $router;
        $this->flash = $flash;
        $this->translation = $translation;
    }

    public function respond($token): ResponseInterface {

        $response = $this->responseFactory->createResponse();

        if ($token !== false) {
            // add token to cookie
            $cookie = SetCookie::create('token')
                    ->withValue($token)
                    ->rememberForever();

            $response = FigResponseCookies::set($response, $cookie);

            return $response->withHeader('Location', $this->router->urlFor('index'))->withStatus(301);
        }

        // wrong login!
        $this->flash->addMessage('message', $this->translation->getTranslatedString("WRONG_LOGIN"));
        $this->flash->addMessage('message_type', 'danger');

        return $response->withHeader('Location', $this->router->urlFor('login'))->withStatus(302);
    }

}
