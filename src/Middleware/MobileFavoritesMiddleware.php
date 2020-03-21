<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\User\MobileFavorites\MobileFavoriteService;

class MobileFavoritesMiddleware {

    protected $logger;
    protected $twig;
    private $mobile_favorites_service;

    public function __construct(LoggerInterface $logger, Twig $twig, MobileFavoriteService $mobile_favorites_service) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->mobile_favorites_service = $mobile_favorites_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $mobile_favorites = $this->mobile_favorites_service->getMobileFavorites();

        // add to view
        $this->twig->getEnvironment()->addGlobal("mobile_favorites", $mobile_favorites);

        return $handler->handle($request);
    }

}
