<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteContext;
use Slim\Routing\RouteParser;
use App\Domain\Admin\Setup\SetupService;
use App\Domain\Settings\SettingsMapper;
use Slim\Views\Twig;
use App\Domain\Main\Translator;
use \App\Application\Action\Main\SetupViewAction;
use \App\Application\Action\Main\SetupRunAction;

class InitialSetupMiddleware {

    protected $logger;
    protected $router;
    protected $setup_service;
    protected $settings_mapper;
    protected $twig;
    protected $translation;
    protected $setup_view_action;
    protected $setup_run_action;

    public function __construct(
        LoggerInterface $logger,
        RouteParser $router,
        SetupService $setup_service,
        SettingsMapper $settings_mapper,
        Twig $twig,
        Translator $translation,
        SetupViewAction $setup_view_action,
        SetupRunAction $setup_run_action
    ) {
        $this->logger = $logger;
        $this->router = $router;
        $this->setup_service = $setup_service;
        $this->settings_mapper = $settings_mapper;
        $this->twig = $twig;
        $this->translation = $translation;
        $this->setup_view_action = $setup_view_action;
        $this->setup_run_action = $setup_run_action;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $hasSettingsTable = $this->settings_mapper->exists();

        if ($hasSettingsTable) {
            return $handler->handle($request);
        }

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $response = new Response();

        $this->twig->getEnvironment()->addGlobal("navigationdrawer_desktophidden", 1);

        if (!is_null($route)) {
            if (!in_array($route->getName(), ["setup", "setup_run"])) {
                $this->logger->info('Redirect to setup page for initial setup');
                return $response->withHeader('Location', $this->router->urlFor('setup'))->withStatus(302);
            } elseif ($route->getName() == "setup") {
                // Call Action directly, skipping the following middlewares (not working when database is empty)
                return ($this->setup_view_action)($request, $response, []);
            } elseif ($route->getName() == "setup_run") {
                // Call Action directly, skipping the following middlewares (not working when database is empty)
                return ($this->setup_run_action)($request, $response, []);
            }
        }

        return $this->twig->render($response, 'error.twig', ["message" => null, "message_type" => "danger"]);
    }
}
