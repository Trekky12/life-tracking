<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

class MobileFavoritesMiddleware {

    protected $twig;

    public function __construct(ContainerInterface $ci) {
        $this->twig = $ci->get('view');
        
        $db = $ci->get('db');
        $translation = $ci->get('translation');
        $user = $ci->get('user_helper')->getUser();
        
        $this->mobile_favorites_mapper = new \App\User\MobileFavorites\Mapper($db, $translation, $user);
    }

    public function __invoke(Request $request, Response $response, $next) {
        $mobile_favorites = $this->mobile_favorites_mapper->getAll('position');
        
        // add to view
        $this->twig->getEnvironment()->addGlobal("mobile_favorites", $mobile_favorites);

        return $next($request, $response);
    }

}
