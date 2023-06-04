<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Dflydev\FigCookies\FigRequestCookies;

class NavigationDrawerMiddleware {

    protected $logger;
    protected $twig;

    public function __construct(LoggerInterface $logger, Twig $twig) {
        $this->logger = $logger;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        /**
         * Get navigation drawer hidden cookie
         */
        $navigationdrawer_desktophidden = FigRequestCookies::get($request, 'navigationdrawer_desktophidden');

        // add to view
        $this->twig->getEnvironment()->addGlobal("navigationdrawer_desktophidden", $navigationdrawer_desktophidden->getValue());

        return $handler->handle($request);
    }

}
