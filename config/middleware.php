<?php

use Slim\App;
use App\Domain\Base\Settings;
use Slim\Csrf\Guard as CSRF;
use App\Application\Error\MyErrorRenderer;
use App\Application\Error\MyErrorHandler;

return function (App $app) {
    $container = $app->getContainer();
    /**
     * this file defines the middlewares registered
     * (The last middleware layer added is the first to be executed)
     */

    /**
     * Hide Navigation Drawer on desktop 
     */
    $app->add('App\Application\Middleware\NavigationDrawerMiddleware');

    /**
     * Get the unread notifications count
     */
    $app->add('App\Application\Middleware\NotificationsMiddleware');

    /**
     * Get the mobile favorites of the user for the UI
     */
    $app->add('App\Application\Middleware\MobileFavoritesMiddleware');

    /**
     * Save last X urls params for redirects after save
     */
    $app->add('App\Application\Middleware\LastQueryParamsMiddleware');

    /**
     * Redirect to initial URI
     */
    $app->add('App\Application\Middleware\RedirectMiddleware');

    /**
     * Restrict access to modules
     */
    $app->add('App\Application\Middleware\ModuleMiddleware');

    /**
     * Check if user needs to change the password
     */
    $app->add('App\Application\Middleware\PWChangeMiddleware');

    /**
     * Save logged In User
     */
    $app->add('App\Application\Middleware\UserMiddleware');

    /**
     * Check if IP is banned
     */
    $app->add('App\Application\Middleware\BanlistMiddleware');

    /**
     * Save Base URL for Links in E-Mail
     */
    $app->add('App\Application\Middleware\BaseURLMiddleware');

    /**
     * Add Cache Miss
     */
    $app->add('App\Application\Middleware\CacheMissMiddleware');

    /**
     * Check if initial setup is done
     */
    $app->add('App\Application\Middleware\InitialSetupMiddleware');

    /**
     * CSRF Protection
     */
    $settings = $container->get(Settings::class);
    if ($settings->all()['CSRF']['enabled']) {
        $app->add($container->get(CSRF::class));
    }

    /**
     * Routing
     */
    $app->addRoutingMiddleware();

    /**
     * Custom Error Renderer
     */
    $errorMiddleware = $app->addErrorMiddleware(true, false, false);

    $myErrorHandler = new MyErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
    $myErrorHandler->registerErrorRenderer('text/html', MyErrorRenderer::class);
    $myErrorHandler->setDefaultErrorRenderer('text/html', MyErrorRenderer::class);

    $errorMiddleware->setDefaultErrorHandler($myErrorHandler);

    // Add Twig-View Middleware
    $app->add('Slim\Views\TwigMiddleware');
};
