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

            $modules = $this->ci->get('settings')['app']['modules'];
            
            $current_module = $this->getCurrentModule($modules, $route);
            
            // Filter only specific routes
            if ($current_module === false) {
                return $next($request, $response);
            }
            
            $hasAccess = $user->hasModule($current_module);
            // Has access
            if (!is_null($user) && $hasAccess) {
                return $next($request, $response);
            }
            // No Access
            $logger = $this->ci->get('logger');
            $logger->addWarning("No Access");

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
    
    private function getCurrentModule($modules, $route){
        foreach($modules as $name => $mod){
            if($this->startsWith($route, $mod['url'])){
                return $name;
            }
        }
        return false;
    }

}
