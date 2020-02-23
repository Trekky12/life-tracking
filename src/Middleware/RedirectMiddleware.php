<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

class RedirectMiddleware {

    protected $helper;
    protected $user_helper;

    public function __construct(ContainerInterface $ci) {
        $this->helper = $ci->get('helper');
        $this->user_helper = $ci->get('user_helper');
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->user_helper->getUser();

        $redirectURI = $this->helper->getSessionVar("redirectURI");
        $uri = $this->helper->getRequestURI($request);

        // do not delete redirectURI when we are on forced pages
        $route = $request->getAttribute('route');
        if (!is_null($route) && !in_array($route->getName(), array("login", "users_change_password"))) {
            $this->helper->deleteSessionVar("redirectURI");

            if (!is_null($user) && !is_null($redirectURI) && $redirectURI !== $uri) {
                return $response->withRedirect($redirectURI, 301);
            }
        }

        return $next($request, $response);
    }

}
