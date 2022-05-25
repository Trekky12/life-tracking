<?php

namespace App\Application\Responder;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Domain\Main\Translator;
use Slim\Views\Twig;
use App\Application\Payload\Payload;

class HTMLTemplateResponder extends HTMLResponder {

    protected $twig;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, Twig $twig) {
        parent::__construct($responseFactory, $translation);
        $this->twig = $twig;
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);
        
        $template = $payload->getTemplate();
        if (!$template) {
            return $response;
        }

        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');

        $result = $payload->getResult();
        $data = !is_null($result) && is_array($result) ? $result : [];

        return $this->twig->render($response, $template, $data);
    }

}
