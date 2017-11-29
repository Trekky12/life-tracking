<?php

// Autoload
require __DIR__ . '/../vendor/autoload.php';


date_default_timezone_set('Europe/Berlin');

// Instantiate the app
$settings = require __DIR__ . '/settings.php';

$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/dependencies.php';

// Register middleware
require __DIR__ . '/middleware.php';

// Register routes
require __DIR__ . '/routes.php';


return $app;
