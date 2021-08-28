<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Domain\Main\Translator;
use Slim\Views\Twig;
use App\Application\Payload\Payload;
use App\Application\Responder\JSONResultResponder;

class JSONHTMLTemplateResponder extends JSONResultResponder {

    private $twig;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, Twig $twig) {
        parent::__construct($responseFactory, $translation);
        $this->twig = $twig;
    }

    public function respond(Payload $payload): ResponseInterface {

        $result = $payload->getResult();
        $data = !is_null($result) ? $result : [];

        switch ($payload->getStatus()) {
            case Payload::$RESULT_HTML:
                $result["data"] = $this->twig->fetch($payload->getTemplate(), $data);
                break;

            case Payload::$NO_ACCESS:
                $result = $this->translation->getTranslatedString("NO_ACCESS");
                break;
        }

        $payload_json = new Payload(Payload::$RESULT_JSON, $result);

        return parent::respond($payload_json);
    }

}
