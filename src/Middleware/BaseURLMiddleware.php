<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

/**
 * Save Base URL for Links in E-Mails
 */
class BaseURLMiddleware {

    protected $helper;
    protected $twig;

    public function __construct(ContainerInterface $ci) {
        $this->helper = $ci->get('helper');
        $this->twig = $ci->get('view');
    }

    public function __invoke(Request $request, Response $response, $next) {

        $host = $request->getUri()->getHost();
        $scheme = $request->getUri()->getScheme();
        $basePath = $request->getUri()->getBasePath();


        if (substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        $path = ($scheme ? $scheme . ':' : '') . ($host ? '//' . $host : '') . rtrim($basePath, '/');

        $this->helper->setBaseURL($path);
        
        $currentURL = $request->getUri()->getPath();
        // add to view
        $this->twig->getEnvironment()->addGlobal("currentURL", $currentURL);

        return $next($request, $response);
    }

}
