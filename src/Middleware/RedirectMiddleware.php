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
        
        // do not delete redirectURI when we are on forced pages
        $route = $request->getAttribute('route');
        if (!is_null($route) && !in_array($route->getName(), array("login", "users_change_password") )) {
            $this->ci->get('helper')->deleteSessionVar("redirectURI");

            if (!is_null($user) && !is_null($redirectURI) && $redirectURI !== $uri) {
                return $response->withRedirect($redirectURI, 301);
            }
        
        }

        return $next($request, $response);
    }

}
