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

        $username = array_key_exists("PHP_AUTH_USER", $_SERVER) ? $_SERVER["PHP_AUTH_USER"] : null;

        $this->ci->get('view')->getEnvironment()->addGlobal('loggedIn', $username);
        
        if (!is_null($username)) {
            $usermapper = new \App\User\Mapper($this->ci);
            try {
                $user = $usermapper->getUserFromLogin($username);
                $this->ci->get('helper')->setUser($user);
                
            } catch (\Exception $e) {
                $logger = $this->ci->get('logger');
                $logger->addInfo('Login FAILED', array('user' => $username, 'error' => $e->getMessage()));
            }
        }
        return $next($request, $response);
    }
}
