<?php

$container = $app->getContainer();

/**
 * Set Settings for global Helper Class
 */
$container['helper'] = function($c) {
    return new \App\Main\Helper($c);
};


/**
 * Flash Messages
 */
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

/**
 * CSRF Guard
 */
$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) use($c) {

        $route = $request->getAttribute('route');
        $allowed_routes = $c['settings']['app']['csrf_exlude'];

        // DELETE currently not working since the body is empty
        if (!is_null($route) && in_array($route->getName(), $allowed_routes)) {
            return $next($request, $response);
        }

        $logger = $c->get('logger');
        $logger->addCritical("Failed CSRF check");

        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $body->write('Failed CSRF check!');
        return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
    });
    return $guard;
};


/**
 * Twig View
 */
$container['view'] = function ($c) {
    $settings = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        //'cache' => $settings[ 'cache_path' ]
        'cache' => false
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
            $c['router'], $c['request']->getUri()
    ));

    $view->addExtension(new Knlv\Slim\Views\TwigMessages(
            //new \Slim\Flash\Messages()
            $c['flash']
    ));

    $view->addExtension(new \App\Main\CsrfExtension(
            $c['csrf']
    ));


    /**
     * Include Translation
     */
    $view->getEnvironment()->addGlobal('lang', $c->get('helper')->getLanguage());

    /**
     * Include Default Location
     */
    $location = $c->get('settings')['app']['location'];
    $view->getEnvironment()->addGlobal('location', $location);

    /**
     * Include Settings
     */
    $i18n = $c->get('settings')['app']['i18n'];
    $view->getEnvironment()->addGlobal('i18n', $i18n);

    /**
     * Include Uploads folder
     */
    $uploads = $c->get('settings')['app']['upload_folder'];
    $view->getEnvironment()->addGlobal('uploads_folder', $uploads);

    /**
     * Include Push settings
     */
    $push = $c->get('settings')['app']['push'];
    $view->getEnvironment()->addGlobal('push', $push);

    /**
     * Include modules
     */
    $modules = $c->get('settings')['app']['modules'];
    $view->getEnvironment()->addGlobal('modules', $modules);

    return $view;
};



/**
 * Monolog
 */
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    $logger->pushProcessor(function ($record) use ($c) {
        $user = $c->get('helper')->getUserLogin();

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
};

/**
 * Database
 */
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $logger = $c->get('logger');
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
};


/**
 * Custom Error Handler
 * @see http://www.slimframework.com/docs/handlers/error.html
 */
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        $logger = $c->get('logger');
        $logger->addCritical($exception->getMessage());

        return $c->get('view')->render($response, 'error.twig', ['message' => $exception->getMessage(), 'message_type' => 'danger']);
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {

        $logger = $c->get('logger');
        $logger->addWarning("Page not found");

        return $c->get('view')->render($response, 'error.twig', ['message' => $c->get('helper')->getTranslatedString("NOTFOUND"), 'message_type' => 'danger']);
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response) use ($c) {

        $logger = $c->get('logger');
        $logger->addWarning("Page not allowed");

        return $c->get('view')->render($response, 'error.twig', ['message' => $c->get('helper')->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
    };
};

$container['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        $logger = $c->get('logger');
        $logger->addCritical($error->getMessage());

        return $c->get('view')->render($response, 'error.twig', ['message' => $error->getMessage(), 'message_type' => 'danger']);
    };
};
