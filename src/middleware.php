<?php

$container = $app->getContainer();
/**
 * this file defines the middlewares registered
 * (The last middleware layer added is the first to be executed)
 */
/**
 * CSRF Protection
 */
$app->add($container->get('csrf'));


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