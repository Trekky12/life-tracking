<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Log\LoggerInterface;
use App\Domain\Main\LoginService;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Main\Utility\Utility;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

class UserMiddleware {

    protected $logger;
    protected $login_service;
    protected $router;
    protected $settings;
    protected $current_user;

    public function __construct(LoggerInterface $logger, LoginService $login_service, RouteParser $router, Settings $settings, CurrentUser $current_user) {
        $this->logger = $logger;
        $this->login_service = $login_service;
        $this->router = $router;
        $this->settings = $settings;
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $response = new Response();

        /**
         * Get and Cache User Object from Token for later use
         */
        $token = FigRequestCookies::get($request, 'token');
        if (!$this->login_service->setUserFromToken($token->getValue())) {
            // token not in database -> delete cookie
            $response = FigResponseCookies::expire($response, 'token');
        }

        /**
         *  Always allow access to guest routes
         */
        $allowed_routes = $this->settings->getAppSettings()['guest_access'];
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        if (!is_null($route) && in_array($route->getName(), $allowed_routes)) {
            return $handler->handle($request);
        }

        /**
         * Check User Object
         */
        $user = $this->current_user->getUser();

        // user is logged in, redirect to next middleware
        if (!is_null($user)) {
            $this->logger->debug('Site CALL');
            return $handler->handle($request);
        }
        // Check for HTTP Authentication 
        if (Utility::startsWith($route->getPattern(), "/api")) {
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
                $this->logger->debug('HTTP Auth', array("user" => $username));
                if ($this->login_service->checkApplicationLogin($username, $password)) {
                    return $handler->handle($request);
                }
                $this->logger->warning('HTTP Auth failed', array("user" => $username));
                
                
                
                $response->getBody()->write('HTTP Auth failed!');
                return $response->withStatus(400)->withHeader('Content-type', 'text/plain');
            }
        }


        $this->logger->debug('Go to Login');

        /**
         * Save target URI for later redirect
         */
        $uri = Utility::getRequestURI($request);
        SessionUtility::setSessionVar("redirectURI", $uri);

        // redirect to the login page
        return $response->withHeader('Location', $this->router->urlFor('login'))->withStatus(302);
    }

}
