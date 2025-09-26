<?php

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Slim\Http\ServerRequest as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Settings\SettingsMapper;

class CacheMissMiddleware {

    protected $logger;
    protected $twig;
    protected $settings_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, SettingsMapper $settings_mapper) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->settings_mapper = $settings_mapper;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface {

        $cachemiss = "";
        $cachemissSetting = $this->settings_mapper->getSetting("cachemiss");
        if(!is_null($cachemissSetting)){
            $cachemiss = $cachemissSetting->getValue();
        }
        
        // add to view
        $this->twig->getEnvironment()->addGlobal("CACHEMISS", $cachemiss);

        return $handler->handle($request);
    }

}
