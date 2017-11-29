<?php

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
    "error" => function ($request, $response, $arguments) use ($container) {
    
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
        $user = $usermapper->getUserFromLogin($username);
        $container->get('helper')->setUser($user);
    }

    return $next($request, $response);
});
