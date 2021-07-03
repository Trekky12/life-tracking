<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteContext;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Domain\Base\Settings;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Csrf\Guard as CSRF;
use Slim\Routing\RouteParser;
use App\Domain\Base\CurrentUser;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Application\TwigExtensions\FlashExtension;
use App\Application\TwigExtensions\CsrfExtension;
use App\Application\TwigExtensions\FontAwesomeExtension;
use App\Domain\Main\Utility\Utility;
use App\Application\Error\CSRFException;

return [
    Settings::class => function () {
        $data = (require __DIR__ . '/../config/settings.php');
        return new Settings($data);
    },
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        return $app;
    },
    /**
     * Twig View
     */
    Twig::class => function (Settings $settings, Flash $flash, CSRF $csrf, Translator $translation) {
        $twig_settings = $settings->all()['view'];

        $twig = Twig::create($twig_settings['template_path'], [
                    //'cache' => $settings[ 'cache_path' ]
                    'cache' => false
        ]);

        $twig->addExtension(new FlashExtension($flash));

        $twig->addExtension(new CsrfExtension($csrf));
        
        $twig->addExtension(new FontAwesomeExtension());


        /**
         * Include Translation
         */
        $twig->getEnvironment()->addGlobal('lang', $translation->getLanguage());

        /**
         * Include Default Location
         */
        $location = $settings->getAppSettings()['location'];
        $twig->getEnvironment()->addGlobal('location', $location);

        /**
         * Include Settings
         */
        $i18n = $settings->getAppSettings()['i18n'];
        $twig->getEnvironment()->addGlobal('i18n', $i18n);

        /**
         * Include Uploads folder
         */
        $uploads = $settings->getAppSettings()['upload_folder'];
        $twig->getEnvironment()->addGlobal('uploads_folder', $uploads);

        /**
         * Include Push settings
         */
        $push = $settings->getAppSettings()['push'];
        $twig->getEnvironment()->addGlobal('push', $push);

        /**
         * Include modules
         */
        $modules = $settings->getAppSettings()['modules'];
        $twig->getEnvironment()->addGlobal('modules', $modules);

        return $twig;
    },
    TwigMiddleware::class => function (ContainerInterface $container) {
        return TwigMiddleware::createFromContainer($container->get(App::class), Twig::class);
    },
    /**
     * Logger
     */
    LoggerInterface::class => function (Settings $settings, CurrentUser $current_user) {
        $logger_settings = $settings->all()['logger'];
        $logger = new Monolog\Logger($logger_settings['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($logger_settings['path'], Monolog\Logger::DEBUG));

        // Add User Entry to Logger
        $logger->pushProcessor(function ($record) use($current_user) {
            if (!is_null($current_user->getUser())) {
                $record['extra']['user'] = $current_user->getUser()->login;
            }

            return $record;
        });

        $extraFields = [
            'url' => 'REQUEST_URI',
            'ip' => 'REMOTE_ADDR',
            'http_method' => 'REQUEST_METHOD',
            'server' => 'SERVER_NAME',
            'referrer' => 'HTTP_REFERER',
            'query' => 'QUERY_STRING',
            'user_agent' => 'HTTP_USER_AGENT'
        ];

        $logger->pushProcessor(new Monolog\Processor\WebProcessor(null, $extraFields));
        return $logger;
    },
    /**
     * Database
     */
    \PDO::class => function (Settings $settings, LoggerInterface $logger) {
        $db_settings = $settings->all()['db'];
        try {
            $pdo = new PDO("mysql:host=" . $db_settings['host'] . ";dbname=" . $db_settings['dbname'], $db_settings['user'], $db_settings['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("set names utf8");
            return $pdo;
        } catch (\PDOException $e) {
            $logger->critical($e->getMessage());
            die("No access to database");
        }
    },
    /**
     * Flash Messages
     */
    Flash::class => function () {
        if (PHP_SAPI === 'cli') {
            $storage = [];
            return new \Slim\Flash\Messages($storage);
        }
        return new \Slim\Flash\Messages();
    },
    /**
     * CSRF Guard
     */
    CSRF::class => function (ContainerInterface $container) {
        $responseFactory = $container->get(App::class)->getResponseFactory();

        if (PHP_SAPI === 'cli' && (strpos($_SERVER['argv'][0], 'phpunit') === false)) {
            $storage = [];
            $guard = new \Slim\Csrf\Guard($responseFactory, 'csrf', $storage);
        } else {
            $guard = new \Slim\Csrf\Guard($responseFactory);
        }
        $guard->setStorageLimit(1000);
        $guard->setFailureHandler(function (Request $request, RequestHandler $handler) use($container): ResponseInterface {

            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            $allowed_routes = $container->get(Settings::class)->all()['CSRF']['exclude'];

            if ((!is_null($route) && (in_array($route->getName(), $allowed_routes) || (Utility::startsWith($route->getPattern(), "/api"))))) {
                return $handler->handle($request);
            }

            $data = $request->getParsedBody();

            $logger = $container->get(LoggerInterface::class);
            $logger->critical("Failed CSRF check", $data);
            
            $ex = new CSRFException();
            $ex->setData($data);
            throw $ex;
        });
        return $guard;
    },
    RouteParser::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    },
    // Response for HTML Responder
    ResponseFactoryInterface::class => function() {
        return new ResponseFactory();
    }
];
