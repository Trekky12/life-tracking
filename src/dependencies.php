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
    'PHP_AUTH_USER' => array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : null,
    'REMOTE_ADDR' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
    'HTTP_USER_AGENT' => array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : null,
    'REQUEST_METHOD' => array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : null,
    'QUERY_STRING' => array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : null,
    'REQUEST_URI' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null
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