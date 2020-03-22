<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Notifications\NotificationsService;

class NotificationsMiddleware {

    protected $logger;
    protected $twig;
    protected $current_user;
    private $notifications_service;

    public function __construct(LoggerInterface $logger, Twig $twig, NotificationsService $notifications_service) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->notifications_service = $notifications_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $unread_notifications = $this->notifications_service->getUnreadNotificationsCountByUser();

        // add to view
        $this->twig->getEnvironment()->addGlobal("unread_notifications", $unread_notifications);

        return $handler->handle($request);
    }

}
