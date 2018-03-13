<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class PWChangeMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->ci->get('helper')->getUser();

        $route = $request->getAttribute('route');


        /**
         * Redirect to change password page 
         */
        if (!is_null($user) && ($user->force_pw_change != 1 || (!is_null($route) && $route->getName() === 'users_change_password'))) {
            return $next($request, $response);
        }
        return $response->withRedirect($this->ci->get('router')->pathFor('users_change_password'), 302);
    }

}
