<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Container\ContainerInterface;

use App\Banlist\Controller as BanListController;

class BanlistMiddleware {

    protected $logger;
    protected $twig;
    protected $helper;
    protected $translation;
    protected $banlistCtrl;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->helper = $ci->get('helper');
        $this->twig = $ci->get('view');
        $this->translation = $ci->get('translation');
        
        $this->banlistCtrl = new BanListController($ci);
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
