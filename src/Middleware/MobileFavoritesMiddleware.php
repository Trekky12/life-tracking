<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\UserHelper;
use App\Main\Translator;

class MobileFavoritesMiddleware {

    protected $logger;
    protected $twig;

    public function __construct(LoggerInterface $logger, Twig $twig, UserHelper $user_helper, \PDO $db, Translator $translation) {
        $this->logger = $logger;
        $this->twig = $twig;

        $user = $user_helper->getUser();

        $this->mobile_favorites_mapper = new \App\User\MobileFavorites\Mapper($db, $translation, $user);
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $mobile_favorites = $this->mobile_favorites_mapper->getAll('position');

        // add to view
        $this->twig->getEnvironment()->addGlobal("mobile_favorites", $mobile_favorites);

        return $handler->handle($request);
    }

}
