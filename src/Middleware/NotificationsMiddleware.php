<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Container\ContainerInterface;

class NotificationsMiddleware {

    protected $user_helper;
    protected $twig;

    public function __construct(ContainerInterface $ci) {
        $this->user_helper = $ci->get('user_helper');
        $this->twig = $ci->get('view');
        
        $db = $ci->get('db');
        $translation = $ci->get('translation');
        $user = $ci->get('user_helper')->getUser();
        
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
