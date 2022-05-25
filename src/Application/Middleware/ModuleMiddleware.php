<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\Utility;

class ModuleMiddleware {

    protected $logger;
    protected $twig;
    protected $settings;
    protected $translation;
    protected $current_user;

    public function __construct(LoggerInterface $logger, Twig $twig, Settings $settings, \PDO $db, Translator $translation, CurrentUser $current_user) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->current_user->getUser();

        $routeContext = RouteContext::fromRequest($request);
        $baseRoute = $routeContext->getRoute();

        if (!is_null($baseRoute)) {
            $route = $baseRoute->getPattern();

            $modules = $this->settings->getAppSettings()['modules'];

            $current_module = $this->getCurrentModule($modules, $route);

            // Filter only specific routes
            if ($current_module === false) {
                return $handler->handle($request);
            }

            $hasAccess = $user->hasModule($current_module);
            // Has access
            if (!is_null($user) && $hasAccess) {
                return $handler->handle($request->withAttribute("module", $current_module));
            }
            // No Access
            $this->logger->warning("No Access");
            $response = new Response();
            return $this->twig->render($response, 'error.twig', ['message' => $this->translation->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
        }
        // Route not found
        return $handler->handle($request);
    }

    private function getCurrentModule($modules, $route) {
        // remove /api prefix 
        //@see https://stackoverflow.com/a/4517270
        if (Utility::startsWith($route, "/api")) {
            $route = substr($route, strlen("/api"));
        }

        foreach ($modules as $name => $mod) {
            if (Utility::startsWith($route, $mod['url'])) {
                return $name;
            }
        }
        return false;
    }

}
