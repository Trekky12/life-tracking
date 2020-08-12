<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

if (isset($_SERVER['REQUEST_METHOD'])) {
    echo "Only CLI allowed. Script stopped.\n";
    exit(1);
}

$container = (require __DIR__ . '/../config/app.php')->getContainer();

$application = new Application();
$application->add($container->get(\App\Domain\Console\CronCommand::class));

$application->run();
