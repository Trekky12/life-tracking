<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class DeleteJSONResponder extends JSONResultResponder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        parent::__construct($responseFactory, $translation);
    }

    public function respond(Payload $payload): ResponseInterface {
        $error = $payload->getResult();

        $response_data = ['is_deleted' => false, 'error' => ''];

        switch ($payload->getStatus()) {
            case Payload::$STATUS_DELETE_SUCCESS:
                $response_data['is_deleted'] = true;
                break;
            case Payload::$STATUS_DELETE_ERROR:
                $response_data['is_deleted'] = false;
                break;

            case Payload::$STATUS_ERROR:
                $response_data['error'] = $this->translation->getTranslatedString($error);
                break;

            case Payload::$NO_ACCESS:
                $response_data['error'] = $this->translation->getTranslatedString("NO_ACCESS");
                break;
        }

        return parent::respond(new Payload(Payload::$RESULT_JSON, $response_data));
    }

}
