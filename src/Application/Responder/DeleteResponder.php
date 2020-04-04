<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use \Slim\Flash\Messages as Flash;

class DeleteResponder extends JSONResultResponder{

    private $flash;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, Flash $flash) {
        parent::__construct($responseFactory, $translation);
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

        return parent::respond(new Payload(Payload::$RESULT_JSON, $response_data));
    }

}
