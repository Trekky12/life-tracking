<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class AdminMiddleware {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->ci->get('helper')->getUser();

        if (!is_null($user) && $user->isAdmin()) {
            return $next($request, $response);
        }

        $logger = $this->ci->get('logger');
        $logger->addWarning("No Admin");

        return $this->ci->get('view')->render($response, 'error.twig', ['message' => $this->ci->get('helper')->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
    }

}
