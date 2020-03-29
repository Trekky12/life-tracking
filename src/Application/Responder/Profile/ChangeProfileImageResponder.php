<?php

namespace App\Application\Responder\Profile;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use Slim\Flash\Messages as Flash;
use App\Application\Responder\Responder;
use App\Application\Payload\Payload;

class ChangeProfileImageResponder extends Responder {

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
            case Payload::$STATUS_PROFILE_IMAGE_DELETED:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("PROFILE_IMAGE_DELETED"));
                $this->flash->addMessage('message_type', 'success');
                break;
            case Payload::$STATUS_PROFILE_IMAGE_ERROR:
                //$this->flash->addMessage('message', $this->translation->getTranslatedString("FILE_UPLOAD_ERROR"));
                //$this->flash->addMessage('message_type', 'danger');
                throw new \Exception($this->translation->getTranslatedString("FILE_UPLOAD_ERROR"));
            case Payload::$STATUS_PROFILE_IMAGE_SET:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("PROFILE_IMAGE_SET"));
                $this->flash->addMessage('message_type', 'success');
                break;
        }
        return $response->withHeader('Location', $this->router->urlFor('users_profile_image'))->withStatus(301);
    }

}
