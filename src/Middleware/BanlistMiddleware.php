<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Translator;
use App\Banlist\BanlistService;
use App\Main\Utility\Utility;

class BanlistMiddleware {

    protected $logger;
    protected $twig;
    protected $translation;
    protected $banlist_service;

    public function __construct(LoggerInterface $logger, Twig $twig, Translator $translation, BanlistService $banlist_service) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->translation = $translation;
        $this->banlist_service = $banlist_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        /**
         * Do not allow access for banned ips
         */
        $isBlocked = $this->banlist_service->isBlocked(Utility::getIP());

        if ($isBlocked) {
            $this->logger->addWarning('BANNED');
            $response = new Response();
            return $this->twig->render($response, 'error.twig', ["message" => $this->translation->getTranslatedString("BANNED"), "message_type" => "danger"]);
        }

        return $handler->handle($request);
    }

}
