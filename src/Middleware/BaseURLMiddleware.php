<?php

namespace App\Middleware;

use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
//use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\ServerRequest as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;

/**
 * Save Base URL for Links in E-Mails
 */
class BaseURLMiddleware {

    protected $logger;
    protected $helper;
    protected $twig;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $host = $request->getUri()->getHost();
        $scheme = $request->getUri()->getScheme();
        $basePath = $request->getUri()->getPath();


        if (substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        $path = ($scheme ? $scheme . ':' : '') . ($host ? '//' . $host : '');

        $this->helper->setBaseURL($path);

        $currentURL = $request->getUri()->getPath();
        // add to view
        $this->twig->getEnvironment()->addGlobal("currentURL", $currentURL);

        return $handler->handle($request);
    }

}
