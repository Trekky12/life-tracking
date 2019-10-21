<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class MobileFavoritesMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->mobile_favorites_mapper = new \App\User\MobileFavorites\Mapper($this->ci);
    }

    public function __invoke(Request $request, Response $response, $next) {
        $mobile_favorites = $this->mobile_favorites_mapper->getAll('position');
        
        // add to view
        $this->ci->get('view')->getEnvironment()->addGlobal("mobile_favorites", $mobile_favorites);

        return $next($request, $response);
    }

}
