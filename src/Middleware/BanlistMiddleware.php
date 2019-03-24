<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class BanlistMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {

        $logger = $this->ci->get('logger');
        
        /**
         * Do not allow access for banned ips
         */
        $banlist = new \App\Main\BanlistMapper($this->ci);
        $attempts = $banlist->getFailedLoginAttempts($this->ci->get('helper')->getIP());

        if ($attempts > 2) {
            $logger->addWarning('BANNED');
            return $this->ci->get('view')->render($response, 'error.twig', ["message" => $this->ci->get('helper')->getTranslatedString("BANNED"), "message_type" => "danger"]);
        }
        
        return $next($request, $response);
    }

}
