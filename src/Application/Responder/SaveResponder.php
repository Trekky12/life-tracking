<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use \Slim\Flash\Messages as Flash;

class SaveResponder {

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

    public function respond($routeName, Payload $payload): ResponseInterface {
        $entry = $payload->getResult();

        switch ($payload->getStatus()) {
            case Payload::$STATUS_NEW:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_ADD"));
                $this->flash->addMessage('message_type', 'success');
                break;
            case Payload::$STATUS_UPDATE:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                $this->flash->addMessage('message_type', 'success');
                break;
            case Payload::$STATUS_NO_UPDATE:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_NOT_CHANGED"));
                $this->flash->addMessage('message_type', 'info');
                break;
            case Payload::$STATUS_PARSING_ERRORS:
                $this->flash->addMessage('message', $this->translation->getTranslatedString($entry->getParsingErrors()[0]));
                $this->flash->addMessage('message_type', 'danger');
                break;
            case Payload::$STATUS_ERROR:
                break;

            case Payload::$RESULT_ARRAY:
                foreach ($entry as $e) {
                    if ($e->getStatus() == Payload::$STATUS_UPDATE) {
                        $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_UPDATE"));
                        $this->flash->addMessage('message_type', 'success');
                    } else if ($e->getStatus() == Payload::$STATUS_NEW) {
                        $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_ADD"));
                        $this->flash->addMessage('message_type', 'success');
                    } else {
                        $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR"));
                        $this->flash->addMessage('message_type', 'danger');
                    }
                }
                break;
        }

        /**
         * Additional Flash Messages (e.g. Budget Check)
         */
        $flash_messages = $payload->getFlashMessages();
        foreach ($flash_messages as $flash_message_type => $flash_message) {
            $this->flash->addMessage($flash_message_type, $flash_message);
        }

        $response = $this->responseFactory->createResponse();
        return $response->withHeader('Location', $this->router->urlFor($routeName))->withStatus(301);
    }

}
