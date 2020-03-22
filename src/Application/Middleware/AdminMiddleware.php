<?php

namespace App\Application\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;

class AdminMiddleware {

    protected $logger;
    protected $twig;
    protected $translation;
    protected $current_user;

    public function __construct(LoggerInterface $logger, Twig $twig, Translator $translation, CurrentUser $current_user) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->translation = $translation;
        $this->current_user = $current_user;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $user = $this->current_user->getUser();

        if (!is_null($user) && $user->isAdmin()) {
            return $handler->handle($request);
        }

        $this->logger->addWarning("No Admin");

        $response = new Response();
        return $this->twig->render($response, 'error.twig', ['message' => $this->translation->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
    }

}
