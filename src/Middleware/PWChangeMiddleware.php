<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

class PWChangeMiddleware {

    protected $logger;
    protected $user_helper;
    protected $router;
    protected $settings;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->user_helper = $ci->get('user_helper');
        $this->router = $ci->get('router');
        $this->settings = $ci->get('settings');
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->user_helper->getUser();

        $route = $request->getAttribute('route');

        $allowed_routes = $this->settings['app']['guest_access'];
        array_push($allowed_routes, 'users_change_password');

        /**
         * Redirect to change password page 
         */
        if ((!is_null($user) && ($user->force_pw_change != 1)) || (!is_null($route) && in_array($route->getName(), $allowed_routes))) {
            return $next($request, $response);
        }

        $this->logger->addWarning("Passwort Change required");

        return $response->withRedirect($this->router->pathFor('users_change_password'), 302);
    }

}
