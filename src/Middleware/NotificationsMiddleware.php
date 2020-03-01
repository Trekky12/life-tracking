<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Translator;
use App\Base\CurrentUser;

class NotificationsMiddleware {

    protected $logger;
    protected $twig;
    protected $current_user;

    public function __construct(LoggerInterface $logger, Twig $twig, \PDO $db, Translator $translation, CurrentUser $current_user) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->current_user = $current_user;

        $this->notifications_mapper = new \App\Notifications\Mapper($db, $translation, $current_user);
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $user = $this->current_user->getUser();

        if (!is_null($user)) {
            $unread_notifications = $this->notifications_mapper->getUnreadNotificationsCountByUser($user->id);

            // add to view
            $this->twig->getEnvironment()->addGlobal("unread_notifications", $unread_notifications);
        }

        return $handler->handle($request);
    }

}
