<?php

namespace App\Application\Responder\Download;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Responder\HTMLTemplateResponder;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use Slim\Views\Twig;

class DownloadResponder extends HTMLTemplateResponder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, Twig $twig) {
        parent::__construct($responseFactory, $translation, $twig);
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $body = $payload->getResult();

        switch ($payload->getStatus()) {
            case Payload::$RESULT_WORD:
                $response->getBody()->write($body);
                return $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                                ->withHeader('Content-Disposition', 'attachment; filename="' . date('Y-m-d') . '_Export.docx"')
                                ->withHeader('Cache-Control', 'max-age=0');
            case Payload::$RESULT_EXCEL:
                $response->getBody()->write($body);
                return $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                                ->withHeader('Content-Disposition', 'attachment; filename="' . date('Y-m-d') . '_Export.xlsx"')
                                ->withHeader('Cache-Control', 'max-age=0');
        }
        
        return $response;
    }

}
