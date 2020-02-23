<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

class UserMiddleware {

    protected $logger;
    protected $helper;
    protected $user_helper;
    protected $router;
    protected $settings;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->helper = $ci->get('helper');
        $this->user_helper = $ci->get('user_helper');
        $this->router = $ci->get('router');
        $this->settings = $ci->get('settings');
    }

    public function __invoke(Request $request, Response $response, $next) {

        /**
         * Get and Cache User Object from Token for later use
         */
        $token = FigRequestCookies::get($request, 'token');
        if (!$this->user_helper->setUserFromToken($token->getValue())) {
            // token not in database -> delete cookie
            $response = FigResponseCookies::expire($response, 'token');
        }

        /**
         *  Always allow access to guest routes
         */
        $allowed_routes = $this->settings['app']['guest_access'];
        $route = $request->getAttribute('route');
        if (!is_null($route) && in_array($route->getName(), $allowed_routes)) {
            return $next($request, $response);
        }

        /**
         * Check User Object
         */
        $user = $this->user_helper->getUser();

        // user is logged in, redirect to next middleware
        if (!is_null($user)) {
            $this->logger->addDebug('Site CALL');
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
                $this->logger->addDebug('HTTP Auth', array("user" => $username));
                if ($this->user_helper->checkLogin($username, $password)) {
                    return $next($request, $response);
                }

                $this->logger->addWarning('HTTP Auth failed', array("user" => $username));
            }
        }

        $this->logger->addDebug('Go to Login');

        /**
         * Save target URI for later redirect
         */
        $uri = $this->helper->getRequestURI($request);
        $this->helper->setSessionVar("redirectURI", $uri);

        // redirect to the login page
        return $response->withRedirect($this->router->pathFor('login'), 302);
    }

}
