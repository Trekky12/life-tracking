<?php

namespace App\Application\Responder\Workouts;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Domain\Main\Translator;
use Slim\Views\Twig;
use App\Application\Payload\Payload;
use App\Application\Responder\JSONResultResponder;

class ExercisesListResponder extends JSONResultResponder {

    private $twig;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, Twig $twig) {
        parent::__construct($responseFactory, $translation);
        $this->twig = $twig;
    }

    public function respond(Payload $payload): ResponseInterface {

        $result = $payload->getResult();
        $data = !is_null($result) ? $result : [];

        $html = $this->twig->fetch($payload->getTemplate(), $data);
        $result["data"] = $html;

        $payload_json = new Payload(Payload::$RESULT_JSON, $result);

        return parent::respond($payload_json);
    }

}
