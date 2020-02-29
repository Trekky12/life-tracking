<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use App\Banlist\Controller as BanListController;

class BanlistMiddleware {

    protected $logger;
    protected $twig;
    protected $helper;
    protected $translation;
    protected $banlistCtrl;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, \PDO $db, Translator $translation) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->twig = $twig;
        $this->translation = $translation;

        $this->banlistCtrl = new BanListController($logger, $twig, $flash, $db, $translation);
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        /**
         * Do not allow access for banned ips
         */
        $isBlocked = $this->banlistCtrl->isBlocked($this->helper->getIP());

        if ($isBlocked) {
            $this->logger->addWarning('BANNED');
            $response = new Response();
            return $this->twig->render($response, 'error.twig', ["message" => $this->translation->getTranslatedString("BANNED"), "message_type" => "danger"]);
        }

        return $handler->handle($request);
    }

}
