<?php

use Slim\App;
use App\Base\Settings;
use Slim\Csrf\Guard as CSRF;

return function (App $app) {
    $container = $app->getContainer();
    /**
     * this file defines the middlewares registered
     * (The last middleware layer added is the first to be executed)
     */
    /**
     * CSRF Protection
     */
    $settings = $container->get(Settings::class);
    if ($settings->all()['CSRF']['enabled']) {
        $app->add($container->get(CSRF::class));
    }

    /**
     * Redirect to initial URI
     */
    $app->add('App\Middleware\RedirectMiddleware');

    /**
     * Restrict access to modules
     */
    $app->add('App\Middleware\ModuleMiddleware');

    /**
     * Check if user needs to change the password
     */
    $app->add('App\Middleware\PWChangeMiddleware');

    /**
     * Get the unread notifications count
     */
    $app->add('App\Middleware\NotificationsMiddleware');

    /**
     * Get the mobile favorites of the user for the UI
     */
    $app->add('App\Middleware\MobileFavoritesMiddleware');

    /**
     * Save logged In User
     */
    $app->add('App\Middleware\UserMiddleware');

    /**
     * Check if IP is banned
     */
    $app->add('App\Middleware\BanlistMiddleware');

    /**
     * Save Base URL for Links in E-Mail
     */
    $app->add('App\Middleware\BaseURLMiddleware');


    /**
     * Routing
     */
    $app->addRoutingMiddleware();

    /**
     * Custom Error Renderer
     */
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    $myErrorHandler = new \App\Main\Error\MyErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
    $myErrorHandler->registerErrorRenderer('text/html', \App\Main\Error\MyErrorRenderer::class);
    $myErrorHandler->setDefaultErrorRenderer('text/html', \App\Main\Error\MyErrorRenderer::class);

    $errorMiddleware->setDefaultErrorHandler($myErrorHandler);

    // Add Twig-View Middleware
    $app->add('Slim\Views\TwigMiddleware');
};
