<?php

$container = $app->getContainer();

/**
 * Set Settings for global Helper Class
 */

$container['helper'] = function($c){
    return new \App\Main\Helper($c);
};


/**
 * Flash Messages
 */
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
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
            new \Slim\Flash\Messages()
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
    return $logger;
};

/**
 * Database
 */
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'], $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("set names utf8");
    return $pdo;
};

$container["info"] = [
    'PHP_AUTH_USER' => array_key_exists('PHP_AUTH_USER', $_SERVER) ? filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING) : null,
    'REMOTE_ADDR' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP),
    'HTTP_USER_AGENT' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING),
    'REQUEST_METHOD' => filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING),
    'QUERY_STRING' => filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING),
    'REQUEST_URI' => filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING),
];

/**
 * Custom Error Handler
 * @see http://www.slimframework.com/docs/handlers/error.html
 */
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        $logger = $c->get('logger');
        $logger->addError($exception->getMessage(), $c["info"]);
        
        return $c->get('view')->render($response, 'error.twig', ['message' => $exception->getMessage(), 'message_type' => 'danger']);
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        
        $logger = $c->get('logger');
        $logger->addInfo("Page not found", $c["info"]);
        
        return $c->get('view')->render($response, 'error.twig', ['message' => $c->get('helper')->getTranslatedString("NOTFOUND"), 'message_type' => 'danger']);
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        
        $logger = $c->get('logger');
        $logger->addInfo("Page not allowed", $c["info"]);
        
        return $c->get('view')->render($response, 'error.twig', ['message' => $c->get('helper')->getTranslatedString("NO_ACCESS"), 'message_type' => 'danger']);
    };
};