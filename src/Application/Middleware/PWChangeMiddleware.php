<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class PWChangeMiddleware {

    protected $logger;
    protected $router;
    protected $settings;
    protected $current_user;

    public function __construct(LoggerInterface $logger, RouteParser $router, Settings $settings, CurrentUser $current_user) {
        $this->logger = $logger;
        $this->router = $router;
        $this->settings = $settings;
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->current_user->getUser();

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $allowed_routes = $this->settings->getAppSettings()['guest_access'];
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
