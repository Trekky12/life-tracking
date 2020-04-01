<?php

namespace App\Application\Responder\NotificationClients;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use Slim\Flash\Messages as Flash;
use App\Application\Responder\Responder;
use App\Application\Payload\Payload;

class TestNotificationResponder extends Responder {

    private $router;
    private $flash;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, RouteParser $router, Flash $flash) {
        parent::__construct($responseFactory, $translation);
        $this->router = $router;
        $this->flash = $flash;
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        switch ($payload->getStatus()) {
            case Payload::$STATUS_NOTIFICATION_SUCCESS:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NOTIFICATION_SEND_SUCCESS"));
                $this->flash->addMessage('message_type', 'success');
                break;
            case Payload::$STATUS_NOTIFICATION_FAILURE:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NOTIFICATION_SEND_FAILURE"));
                $this->flash->addMessage('message_type', 'danger');
                break;
        }
        return $response->withHeader('Location', $this->router->urlFor('notifications_clients'))->withStatus(301);
    }

}
