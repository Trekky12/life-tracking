<?php

$container = $app->getContainer();
$info = [
    'PHP_AUTH_USER' => array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : null,
    'REMOTE_ADDR' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
    'HTTP_USER_AGENT' => array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : null,
    'REQUEST_METHOD' => array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : null,
    'QUERY_STRING' => array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : null,
    'REQUEST_URI' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null
];

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
    "callback" => function ($request, $response, $arguments) use ($container, $info){
        $logger = $container->get('logger');
        $logger->addInfo('SITE CALL', $info);
    },
    "error" => function ($request, $response, $arguments) use ($container, $info) {
        
        if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
            
            $info['PHP_AUTH_PW'] = array_key_exists('PHP_AUTH_PW', $_SERVER) ? $_SERVER['PHP_AUTH_PW'] : null;

            $logger = $container->get('logger');
            $logger->addInfo('Login FAILED', $info);
        }
        
        return $container['view']->render($response, 'error.twig', ["message" => $arguments["message"], "message_type" => "danger"]);
    }
]));

/**
 * Save logged In User
 */
$app->add(function ($request, $response, $next) use ($container) {

    $username = array_key_exists("PHP_AUTH_USER", $_SERVER) ? $_SERVER["PHP_AUTH_USER"] : null;

    $container->get('view')->getEnvironment()->addGlobal('loggedIn', $username);
    
    if (!is_null($username)) {
        $usermapper = new \App\User\Mapper($container, 'users');
        try{
            $user = $usermapper->getUserFromLogin($username);
            $container->get('helper')->setUser($user);
        }catch (\Exception $e){
            $logger = $container->get('logger');
            $logger->addInfo('Login FAILED', array('user' => $username, 'error' => $e->getMessage()));
        }
    }

    return $next($request, $response);
});
