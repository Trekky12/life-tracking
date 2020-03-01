<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use App\Main\Helper;
use App\Base\CurrentUser;

class RedirectMiddleware {

    protected $helper;
    protected $current_user;

    public function __construct(Helper $helper, CurrentUser $current_user) {
        $this->helper = $helper;
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->current_user->getUser();

        $redirectURI = $this->helper->getSessionVar("redirectURI");
        $uri = $this->helper->getRequestURI($request);

        // do not delete redirectURI when we are on forced pages
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        if (!is_null($route) && !in_array($route->getName(), array("login", "users_change_password"))) {
            $this->helper->deleteSessionVar("redirectURI");

            if (!is_null($user) && !is_null($redirectURI) && $redirectURI !== $uri) {
                $response = new Response();
                return $response->withHeader('Location', $redirectURI)->withStatus(301);
            }
        }

        return $handler->handle($request);
    }

}
