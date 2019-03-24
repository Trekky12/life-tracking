<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class RedirectMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->ci->get('helper')->getUser();
        
        $redirectURI = $this->ci->get('helper')->getSessionVar("redirectURI");
        $uri = $this->ci->get('helper')->getRequestURI($request);
        
        $this->ci->get('helper')->deleteSessionVar("redirectURI");

        if (!is_null($user) && !is_null($redirectURI) && $redirectURI !== $uri) {
            return $response->withRedirect($redirectURI, 301);
        }

        return $next($request, $response);
    }

}
