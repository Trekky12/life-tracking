<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use Psr\Container\ContainerInterface;

class ModuleMiddleware {

    protected $logger;
    protected $twig;
    protected $user_helper;
    protected $settings;
    protected $translation;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->user_helper = $ci->get('user_helper');
        $this->twig = $ci->get('view');
        $this->settings = $ci->get('settings');
        $this->translation = $ci->get('translation');
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->user_helper->getUser();

        $routeContext = RouteContext::fromRequest($request);
        $baseRoute = $routeContext->getRoute();

        if (!is_null($baseRoute)) {
            $route = $baseRoute->getPattern();

            $modules = $this->settings['app']['modules'];

            $current_module = $this->getCurrentModule($modules, $route);

            // Filter only specific routes
            if ($current_module === false) {
                return $handler->handle($request);
            }

            $hasAccess = $user->hasModule($current_module);
            // Has access
            if (!is_null($user) && $hasAccess) {
                return $handler->handle($request);
            }
            // No Access
            $this->logger->addWarning("No Access");
            $response = new Response();
            return $this->twig->render($response, 'error.twig', ['message' => $this->translation->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
        }
        // Route not found
        return $handler->handle($request);
    }

    /**
     * @see https://stackoverflow.com/a/834355
     */
    private function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    private function getCurrentModule($modules, $route){
        foreach($modules as $name => $mod){
            if($this->startsWith($route, $mod['url'])){
                return $name;
            }
        }
        return false;
    }

}
