<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use \Slim\Flash\Messages as Flash;

class DeleteResponder extends JSONResponder {

    private $flash;
    private $translation;

    public function __construct(ResponseFactoryInterface $responseFactory, Flash $flash, Translator $translation) {
        parent::__construct($responseFactory);
        $this->flash = $flash;
        $this->translation = $translation;
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
        }
        
        return parent::respond(new Payload(null, $response_data));
    }

}
