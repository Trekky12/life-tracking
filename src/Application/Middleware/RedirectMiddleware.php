<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Main\Utility\Utility;

class RedirectMiddleware {

    protected $current_user;

    public function __construct(CurrentUser $current_user) {
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->current_user->getUser();

        $redirectURI = SessionUtility::getSessionVar("redirectURI");
        $uri = Utility::getRequestURI($request);

        // do not delete redirectURI when we are on forced pages
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        if (!is_null($route) && !in_array($route->getName(), array("login", "users_change_password"))) {
            SessionUtility::deleteSessionVar("redirectURI");

            if (!is_null($user) && !is_null($redirectURI) && $redirectURI !== $uri) {
                $response = new Response();
                return $response->withHeader('Location', $redirectURI)->withStatus(301);
            }
        }

        return $handler->handle($request);
    }

}
