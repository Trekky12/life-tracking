<?php

namespace App\Application\Responder\Profile;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use Slim\Flash\Messages as Flash;
use App\Application\Responder\Responder;
use App\Application\Payload\Payload;
use Slim\Views\Twig;

class ChangePasswordResponder extends Responder {

    private $router;
    private $flash;
    private $twig;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, RouteParser $router, Flash $flash, Twig $twig) {
        parent::__construct($responseFactory, $translation);
        $this->router = $router;
        $this->flash = $flash;
        $this->twig = $twig;
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        switch ($payload->getStatus()) {
            case Payload::$STATUS_PASSWORD_MISSMATCH:
                $this->flash->addMessageNow('message', $this->translation->getTranslatedString("PASSWORD1AND2MUSTMATCH"));
                $this->flash->addMessageNow('message_type', 'danger');
                return $this->twig->render($response, 'profile/changepw.twig');
            case Payload::$STATUS_PASSWORD_WRONG:
                $this->flash->addMessageNow('message', $this->translation->getTranslatedString("PASSWORD_WRONG_OLD"));
                $this->flash->addMessageNow('message_type', 'danger');
                return $this->twig->render($response, 'profile/changepw.twig');
            case Payload::$STATUS_PASSWORD_SUCCESS:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("PASSWORD_CHANGE_SUCCESS"));
                $this->flash->addMessage('message_type', 'success');
                return $response->withHeader('Location', $this->router->urlFor('index'))->withStatus(301);
        }
    }

}
