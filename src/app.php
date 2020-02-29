<?php

use DI\ContainerBuilder;
use Slim\App;

// Autoload
require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Berlin');

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(__DIR__ . '/container.php');
$container = $containerBuilder->build();

$app = $container->get(App::class);

// Register middleware
(require __DIR__ . '/middleware.php')($app);

// Register routes
(require __DIR__ . '/routes.php')($app);

return $app;
