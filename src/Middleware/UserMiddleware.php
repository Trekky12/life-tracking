<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Main\UserHelper;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;

class UserMiddleware {

    protected $logger;
    protected $helper;
    protected $user_helper;
    protected $router;
    protected $settings;
    protected $current_user;

    public function __construct(LoggerInterface $logger, Helper $helper, UserHelper $user_helper, RouteParser $router, Settings $settings, CurrentUser $current_user) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->user_helper = $user_helper;
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
        if (!$this->user_helper->setUserFromToken($token->getValue())) {
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
            $this->logger->addDebug('Site CALL');
            return $handler->handle($request);
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
                    return $handler->handle($request);
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
        return $response->withHeader('Location', $this->router->urlFor('login'))->withStatus(302);
    }

}
