<?php

namespace App\Application\Responder\Crawlers;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteParser;
use App\Domain\Main\Translator;
use Slim\Flash\Messages as Flash;
use App\Application\Responder\Responder;
use App\Application\Payload\Payload;

class CrawlersDeleteOldResponder extends Responder {

    private $router;
    private $flash;

    public function __construct(ResponseFactoryInterface $responseFactory, Translator $translation, RouteParser $router, Flash $flash) {
        parent::__construct($responseFactory, $translation);
        $this->router = $router;
        $this->flash = $flash;
    }

    public function respond(Payload $payload): ResponseInterface {
        $response = parent::respond($payload);

        $data = $payload->getResult();

        switch ($payload->getStatus()) {
            case Payload::$STATUS_ERROR:
                $this->flash->addMessage('message', $this->translation->getTranslatedString($data["error"]));
                $this->flash->addMessage('message_type', 'danger');
                break;
            default:
                $this->flash->addMessage('message', $this->translation->getTranslatedString("CRAWLER_DATASETS_DELETED", ['%count%' => $data["count"]]));
                $this->flash->addMessage('message_type', 'success');
        }
        return $response->withHeader('Location', $this->router->urlFor('crawlers_view', ["crawler" => $data["crawler"]]))->withStatus(301);
    }

}
