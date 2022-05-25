<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use \Slim\Flash\Messages as Flash;
use Slim\Routing\RouteParser;
use App\Domain\Main\Utility\LastURLsUtility;

class DeleteResponder extends JSONResultResponder {

    private $router;
    private $flash;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, RouteParser $router, Flash $flash) {
        parent::__construct($responseFactory, $translation);
        $this->router = $router;
        $this->flash = $flash;
    }

    public function respond(Payload $payload): ResponseInterface {
        $error = $payload->getResult();

        $response_data = ['is_deleted' => false, 'error' => ''];

        switch ($payload->getStatus()) {
            case Payload::$STATUS_DELETE_SUCCESS:
                $response_data['is_deleted'] = true;

                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_SUCCESS_DELETE"));
                $this->flash->addMessage('message_type', 'success');
                break;
            case Payload::$STATUS_DELETE_ERROR:
                $response_data['is_deleted'] = false;
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->flash->addMessage('message_type', 'danger');
                break;

            case Payload::$STATUS_ERROR:
                $response_data['error'] = $this->translation->getTranslatedString($error);
                $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR_DELETE"));
                $this->flash->addMessage('message_type', 'danger');
                break;

            case Payload::$NO_ACCESS:
                $response_data['error'] = $this->translation->getTranslatedString("NO_ACCESS");
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NO_ACCESS"));
                $this->flash->addMessage('message_type', 'danger');
                break;
        }


        if (!is_null($payload->getRouteName())) {

            $queryParams = LastURLsUtility::getLastURLsForRoute($payload->getRouteName(), $payload->getRouteParams());

            $response_data["redirect"] = $this->router->urlFor($payload->getRouteName(), $payload->getRouteParams(), $queryParams);
        }

        return parent::respond(new Payload(Payload::$RESULT_JSON, $response_data));
    }

}
