<?php

$container = $app->getContainer();
$info = [
    'PHP_AUTH_USER' => array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : null,
    'PHP_AUTH_PW' => array_key_exists('PHP_AUTH_PW', $_SERVER) ? $_SERVER['PHP_AUTH_PW'] : null,
    'HTTP_AUTHORIZATION' => array_key_exists('HTTP_AUTHORIZATION', $_SERVER) ? $_SERVER['HTTP_AUTHORIZATION'] : null,
    'REMOTE_ADDR' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null,
    'HTTP_USER_AGENT' => array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : null,
    'HTTP_X_FORWARDED_FOR' => array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null,
    'HTTP_CLIENT_IP' => array_key_exists('HTTP_CLIENT_IP', $_SERVER) ? $_SERVER['HTTP_CLIENT_IP'] : null,
    'REQUEST_METHOD' => array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : null,
    'QUERY_STRING' => array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : null,
    'REQUEST_URI' => array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null,
    'SCRIPT_NAME' => array_key_exists('SCRIPT_NAME', $_SERVER) ? $_SERVER['SCRIPT_NAME'] : null,
];

if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches))
{
    list($name, $password) = explode(':', base64_decode($matches[1]));
    $info['PHP_AUTH_USER'] = strip_tags($name);
    $info['PHP_AUTH_PW'] = strip_tags($password);
}

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
        
        $logger = $container->get('logger');
        $logger->addInfo('Login FAILED', $info);
        
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
