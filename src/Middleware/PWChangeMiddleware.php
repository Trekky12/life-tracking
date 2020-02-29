<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
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

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->user_helper->getUser();

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $allowed_routes = $this->settings['app']['guest_access'];
        array_push($allowed_routes, 'users_change_password');

        /**
         * Redirect to change password page 
         */
        if ((!is_null($user) && ($user->force_pw_change != 1)) || (!is_null($route) && in_array($route->getName(), $allowed_routes))) {
            return $handler->handle($request);
        }

        $this->logger->addWarning("Passwort Change required");
        
        $response = new Response();
        return $response->withHeader('Location', $this->router->urlFor('users_change_password'))->withStatus(302);
    }

}
