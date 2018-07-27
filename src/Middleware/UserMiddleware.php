<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class UserMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {


        /**
         * Do not allow access for banned ips
         */
        $logger = $this->ci->get('logger');
        $info = $this->ci->get('info');
        //$logger->addInfo('SITE CALL', $info);

        $banlist = new \App\Main\BanlistMapper($this->ci);
        $attempts = $banlist->getFailedLoginAttempts($info["REMOTE_ADDR"]);

        if ($attempts > 2) {
            $logger->addInfo('BANNED', $info);
            return $this->ci->get('view')->render($response, 'error.twig', ["message" => $this->ci->get('helper')->getTranslatedString("BANNED"), "message_type" => "danger"]);
        }


        /**
         *  Always allow access to guest routes
         */
        $allowed_routes = $this->ci->get('settings')['app']['guest_access'];
        $route = $request->getAttribute('route');
        if (!is_null($route) && in_array($route->getName(), $allowed_routes)) {
            return $next($request, $response);
        }


        $user = $this->ci->get('helper')->getUser();

        // user is logged in, redirect to next middleware
        if (!is_null($user)) {

            $logger->addInfo('Site CALL', $info);

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

            if ($this->ci->get('helper')->checkLogin($username, $password)) {
                return $next($request, $response);
            }
        }

        // redirect to the login page
        return $response->withRedirect($this->ci->get('router')->pathFor('login'), 302);
    }

}
