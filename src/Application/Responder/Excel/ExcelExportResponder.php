<?php

namespace App\Application\Responder\Excel;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use App\Application\Responder\HTMLResponder;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;

class ExcelExportResponder extends HTMLResponder {

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation) {
        parent::__construct($responseFactory, $translation);
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $body = $payload->getResult();

        $response->getBody()->write($body);

        return $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . date('Y-m-d') . '_Export.xlsx"')
                        ->withHeader('Cache-Control', 'max-age=0');
    }

}
