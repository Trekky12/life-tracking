<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class ModuleMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->ci->get('helper')->getUser();

        $baseRoute = $request->getAttribute('route');

        if (!is_null($baseRoute)) {
            $route = $baseRoute->getPattern();

            // Filter only specific routes
            if (!$this->startsWith($route, '/finances') && !$this->startsWith($route, '/location') && !$this->startsWith($route, '/cars') && !$this->startsWith($route, '/boards')) {
                return $next($request, $response);
            }

            // Has access
            if (!is_null($user) && (
                    $this->startsWith($route, '/finances') && $user->module_finance == 1 ||
                    $this->startsWith($route, '/location') && $user->module_location == 1 ||
                    $this->startsWith($route, '/cars') && $user->module_cars == 1 ||
                    $this->startsWith($route, '/boards') && $user->module_boards == 1
                    )) {
                return $next($request, $response);
            }
            // No Access
            return $this->ci->get('view')->render($response, 'error.twig', ['message' => $this->ci->get('helper')->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
        }
        // Route not found
        return $next($request, $response);
    }

    /**
     * @see https://stackoverflow.com/a/834355
     */
    private function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

}
