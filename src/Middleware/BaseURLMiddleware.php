<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class BaseURLMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {

        /**
         * Save Base URL
         */
        $host = $request->getUri()->getHost();
        $scheme = $request->getUri()->getScheme();
        $basePath = $request->getUri()->getBasePath();


        if (substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        $path =  ($scheme ? $scheme . ':' : '') . ($host ? '//' . $host : '') . rtrim($basePath, '/');

        $this->ci->get('helper')->setPath($path);

        return $next($request, $response);
    }

}
