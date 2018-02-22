<?php

/**
 * this file defines the middlewares registered
 * (The last middleware layer added is the first to be executed)
 */
/**
 * Restrict sccess of modules
 */
$app->add('App\Middleware\ModuleMiddleware');

/**
 * Save logged In User
 * 
 */
$app->add('App\Middleware\UserMiddleware');


/**
 * Basic Auth
 */
$container = $app->getContainer();

$pdo = $container->get('db');

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => ["/"],
    "realm" => "Protected",
    "secure" => false,
    "authenticator" => new \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator([
        "pdo" => $pdo,
        "table" => "users",
        "user" => "login",
        "hash" => "password"
            ]),
    "callback" => function ($request, $response, $arguments) use ($container) {
        $logger = $container->get('logger');
        $info = $container->get('info');
        $logger->addInfo('SITE CALL', $info);

        /**
         * Do not allow access for banned ips
         */
        $banlist = new \App\Main\BanlistMapper($container);
        $attempts = $banlist->getFailedLoginAttempts($info["REMOTE_ADDR"]);

        if ($attempts > 2) {
            return false;
        } else {
            $banlist->deleteFailedLoginAttempts($info["REMOTE_ADDR"]);
        }
    },
    "error" => function ($request, $response, $arguments) use ($container) {

        $info = $container->get('info');

        if (array_key_exists('PHP_AUTH_PW', $_SERVER)) {

            $info['PHP_AUTH_PW'] = filter_var($_SERVER['PHP_AUTH_PW'], FILTER_SANITIZE_STRING);

            $logger = $container->get('logger');
            $logger->addInfo('Login FAILED', $info);
        }

        /**
         * Log failed login to database
         */
        if (!is_null($info["PHP_AUTH_USER"]) && !is_null($info["REMOTE_ADDR"])) {
            $banlist = new \App\Main\BanlistMapper($container);
            $model = new \App\Base\Model(array('ip' => $info["REMOTE_ADDR"], 'username' => $info["PHP_AUTH_USER"]));
            $banlist->insert($model);
        }

        $message = $arguments["message"] == "Callback returned false" ? "BANNED" : "NO_ACCESS";

        return $container['view']->render($response, 'error.twig', ["message" => $container->get('helper')->getTranslatedString($message), "message_type" => "danger"]);
    }
]));

