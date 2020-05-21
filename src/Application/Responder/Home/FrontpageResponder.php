<?php

namespace App\Application\Responder\Home;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use App\Application\Responder\Responder;
use App\Application\Payload\Payload;
use Slim\Views\Twig;

class FrontpageResponder extends Responder {

    private $router;
    private $twig;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, RouteParser $router, Twig $twig) {
        parent::__construct($responseFactory, $translation);
        $this->router = $router;
        $this->twig = $twig;
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $result = $payload->getResult();
        if ($payload->getStatus() == Payload::$STATUS_HAS_START_URL) {
            return $response->withHeader('Location', $result)->withStatus(301);
        }
        return $this->twig->render($response, $payload->getTemplate(), !is_null($result) ? $result : []);
    }

}
