<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

class AdminMiddleware {

    protected $logger;
    protected $twig;
    protected $user_helper;
    protected $translation;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->user_helper = $ci->get('user_helper');
        $this->twig = $ci->get('view');
        $this->translation = $ci->get('translation');
    }

    public function __invoke(Request $request, Response $response, $next) {

        $user = $this->user_helper->getUser();

        if (!is_null($user) && $user->isAdmin()) {
            return $next($request, $response);
        }

        $this->logger->addWarning("No Admin");

        return $this->twig->render($response, 'error.twig', ['message' => $this->translation->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
    }

}
