<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
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

    public function __invoke(Request $request, Response $response, $next) {
        
        /**
         * Do not allow access for banned ips
         */
        
        $isBlocked = $this->banlistCtrl->isBlocked($this->helper->getIP());

        if ($isBlocked) {
            $this->logger->addWarning('BANNED');
            return $this->twig->render($response, 'error.twig', ["message" => $this->translation->getTranslatedString("BANNED"), "message_type" => "danger"]);
        }
        
        return $next($request, $response);
    }

}
