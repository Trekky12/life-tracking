<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Main\Helper;
use App\Base\Settings;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use App\Main\UserHelper;
use Slim\Csrf\Guard as CSRF;
use Slim\Routing\RouteParser;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

return [
    Settings::class => function () {
        $data = (require __DIR__ . '/settings.php');
        return new \App\Base\Settings($data);
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

        $twig->addExtension(new \App\Main\FlashExtension($flash));

        $twig->addExtension(new \App\Main\CsrfExtension($csrf));


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
    LoggerInterface::class => function (Settings $settings) {
        $logger_settings = $settings->all()['logger'];
        $logger = new Monolog\Logger($logger_settings['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($logger_settings['path'], Monolog\Logger::DEBUG));

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
            $logger->addCritical($e->getMessage());
            die("No access to database");
        }
    },
    /**
     * Set Settings for global Helper Class
     */
    Helper::class => function (LoggerInterface $logger, Twig $twig, Settings $settings) {
        return new \App\Main\Helper($logger, $twig, $settings);
    },
    /**
     * User Helper
     */
    UserHelper::class => function (LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, Settings $settings, \PDO $db, Translator $translation) {
        return new \App\Main\UserHelper($logger, $twig, $helper, $flash, $settings, $db, $translation);
    },
    /**
     * Activity Handler
     */
    Activity::class => function (LoggerInterface $logger, Twig $twig, UserHelper $user_helper, Settings $settings, \PDO $db, Translator $translation) {
        return new \App\Activity\Controller($logger, $twig, $user_helper, $settings, $db, $translation);
    },
    /**
     * Flash Messages
     */
    Flash::class => function () {
        return new \Slim\Flash\Messages();
    },
    /**
     * Translations
     */
    Translator::class => function (Settings $settings) {
        return new \App\Main\Translator($settings);
    },
    /**
     * CSRF Guard
     */
    CSRF::class => function (ContainerInterface $container) {
        $responseFactory = $container->get(App::class)->getResponseFactory();
        $guard = new \Slim\Csrf\Guard($responseFactory);
        $guard->setFailureHandler(function (Request $request, RequestHandler $handler) use($container): ResponseInterface {

                    $routeContext = RouteContext::fromRequest($request);
                    $route = $routeContext->getRoute();
                    $allowed_routes = $container->get(Settings::class)->all()['CSRF']['exclude'];

                    if ((!is_null($route) && in_array($route->getName(), $allowed_routes))) {
                        return $handler->handle($request);
                    }

                    $logger = $container->get(LoggerInterface::class);
                    $logger->addCritical("Failed CSRF check");

                    $response = new Response();
                    $response->getBody()->write('Failed CSRF check!');
                    return $response->withStatus(400)->withHeader('Content-type', 'text/plain');
                });
        return $guard;
    },
    'router' => DI\get('Slim\Routing\RouteParser'),
    RouteParser::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    }
];
