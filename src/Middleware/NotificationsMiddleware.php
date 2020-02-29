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

class NotificationsMiddleware {

    protected $logger;
    protected $user_helper;
    protected $twig;

    public function __construct(LoggerInterface $logger, Twig $twig, UserHelper $user_helper, \PDO $db, Translator $translation) {
        $this->logger = $logger;
        $this->user_helper = $user_helper;
        $this->twig = $twig;

        $user = $this->user_helper->getUser();

        $this->notifications_mapper = new \App\Notifications\Mapper($db, $translation, $user);
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {
        $user = $this->user_helper->getUser();

        if (!is_null($user)) {
            $unread_notifications = $this->notifications_mapper->getUnreadNotificationsCountByUser($user->id);

            // add to view
            $this->twig->getEnvironment()->addGlobal("unread_notifications", $unread_notifications);
        }

        return $handler->handle($request);
    }

}
