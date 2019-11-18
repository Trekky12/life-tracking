<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class NotificationsMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->notifications_mapper = new \App\Notifications\Mapper($this->ci);
    }

    public function __invoke(Request $request, Response $response, $next) {
        $user = $this->ci->get('helper')->getUser();

        if (!is_null($user)) {
            $unread_notifications = $this->notifications_mapper->getUnreadNotificationsCountByUser($user->id);

            // add to view
            $this->ci->get('view')->getEnvironment()->addGlobal("unread_notifications", $unread_notifications);
        }

        return $next($request, $response);
    }

}
