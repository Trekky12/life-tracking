<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Main\Helper;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as Response;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

return [
    'settings' => function (ContainerInterface $container) {
        return (require __DIR__ . '/settings.php');
    },
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        return $app;
    },
    /**
     * Twig View
     */
    'view' => DI\get('Slim\Views\Twig'),
    Twig::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['view'];

        $twig = Twig::create($settings['template_path'], [
//'cache' => $settings[ 'cache_path' ]
                    'cache' => false
        ]);

        $twig->addExtension(new \App\Main\FlashExtension(
                        $container->get('flash')
        ));

        $twig->addExtension(new \App\Main\CsrfExtension(
                        $container->get('csrf')
        ));


        /**
         * Include Translation
         */
        $twig->getEnvironment()->addGlobal('lang', $container->get('translation')->getLanguage());

        /**
         * Include Default Location
         */
        $location = $container->get('settings')['app']['location'];
        $twig->getEnvironment()->addGlobal('location', $location);

        /**
         * Include Settings
         */
        $i18n = $container->get('settings')['app']['i18n'];
        $twig->getEnvironment()->addGlobal('i18n', $i18n);

        /**
         * Include Uploads folder
         */
        $uploads = $container->get('settings')['app']['upload_folder'];
        $twig->getEnvironment()->addGlobal('uploads_folder', $uploads);

        /**
         * Include Push settings
         */
        $push = $container->get('settings')['app']['push'];
        $twig->getEnvironment()->addGlobal('push', $push);

        /**
         * Include modules
         */
        $modules = $container->get('settings')['app']['modules'];
        $twig->getEnvironment()->addGlobal('modules', $modules);

        return $twig;
    },
    TwigMiddleware::class => function (ContainerInterface $container) {
        return TwigMiddleware::createFromContainer($container->get(App::class));
    },
    /**
     * Logger
     */
    'logger' => DI\get('Psr\Log\LoggerInterface'),
    LoggerInterface::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new Monolog\Logger($settings['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
        $logger->pushProcessor(function ($record) use ($container) {
                    $user = $container->get('user_helper')->getUserLogin();

                    if (!is_null($user)) {
                        $record['extra']['user'] = $user;
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
    'db' => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];
        $logger = $container->get('logger');
        try {
            $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'], $settings['user'], $settings['pass']);
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
    'helper' => DI\get('App\Main\Helper'),
    Helper::class => function (ContainerInterface $container) {
        return new \App\Main\Helper($container);
    },
    /**
     * Activity Handler
     */
    'activity' => function (ContainerInterface $container) {
        return new \App\Activity\Controller($container);
    },
    /**
     * Flash Messages
     */
    'flash' => function (ContainerInterface $container) {
        return new \Slim\Flash\Messages();
    },
    /**
     * Translations
     */
    'translation' => function (ContainerInterface $container) {
        return new \App\Main\Translator($container);
    },
    /**
     * Translations
     */
    'user_helper' => function (ContainerInterface $container) {
        return new \App\Main\UserHelper($container);
    },
    /**
     * CSRF Guard
     */
    'csrf' => function (ContainerInterface $container) {
        $responseFactory = $container->get(App::class)->getResponseFactory();
        $guard = new \Slim\Csrf\Guard($responseFactory);
        $guard->setFailureHandler(function (Request $request, RequestHandler $handler) use($container): ResponseInterface {

                    $routeContext = RouteContext::fromRequest($request);
                    $route = $routeContext->getRoute();
                    $allowed_routes = $container->get('settings')['CSRF']['exclude'];

                    if ((!is_null($route) && in_array($route->getName(), $allowed_routes))) {
                        return $handler->handle($request);
                    }

                    $logger = $container->get('logger');
                    $logger->addCritical("Failed CSRF check");

                    $response = new Response();
                    $response->getBody()->write('Failed CSRF check!');
                    return $response->withStatus(400)->withHeader('Content-type', 'text/plain');
                });
        return $guard;
    },
    'router' => function (ContainerInterface $container) {
        return $container->get(App::class)->getRouteCollector()->getRouteParser();
    }
];
