<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

class UserMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {
        $logger = $this->ci->get('logger');
    
        /**
         * Get and Cache User Object from Token for later use
         */
        $token = FigRequestCookies::get($request, 'token');        
        if(!$this->ci->get('helper')->setUserFromToken($token->getValue())){
            // token not in database -> delete cookie
            $response = FigResponseCookies::expire($response, 'token');
        }

        /**
         *  Always allow access to guest routes
         */
        $allowed_routes = $this->ci->get('settings')['app']['guest_access'];
        $route = $request->getAttribute('route');
        if (!is_null($route) && in_array($route->getName(), $allowed_routes)) {
            return $next($request, $response);
        }

        /**
         * Check User Object
         */
        $user = $this->ci->get('helper')->getUser();
        
        // user is logged in, redirect to next middleware
        if (!is_null($user)) {
            $logger->addDebug('Site CALL');
            return $next($request, $response);
        }
        // Check for HTTP Authentication
        else {

            $server_params = $request->getServerParams();

            $username = null;
            $password = null;

            if (isset($server_params["HTTP_AUTHORIZATION"])) {
                if (preg_match("/Basic\s+(.*)$/i", $server_params["HTTP_AUTHORIZATION"], $matches)) {
                    list($username, $password) = explode(":", base64_decode($matches[1]), 2);
                }
            } else {
                if (isset($server_params["PHP_AUTH_USER"])) {
                    $username = $server_params["PHP_AUTH_USER"];
                }
                if (isset($server_params["PHP_AUTH_PW"])) {
                    $password = $server_params["PHP_AUTH_PW"];
                }
            }

            if (!is_null($username) && !is_null($password)) {
                $logger->addDebug('HTTP Auth', array("user" => $username));
                if ($this->ci->get('helper')->checkLogin($username, $password)) {
                    return $next($request, $response);
                }

                $logger->addWarning('HTTP Auth failed', array("user" => $username));
            }
        }
        
        $logger->addDebug('Go to Login');
        
        /**
         * Save target URI for later redirect
         */
        
        $uri = $this->ci->get('helper')->getRequestURI($request);
        $this->ci->get('helper')->setSessionVar("redirectURI", $uri);

        // redirect to the login page
        return $response->withRedirect($this->ci->get('router')->pathFor('login'), 302);
    }

}
