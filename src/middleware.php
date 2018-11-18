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
 * Restrict sccess of modules
 */
$app->add('App\Middleware\ModuleMiddleware');

/**
 * Check if user needs to change the password
 */
$app->add('App\Middleware\PWChangeMiddleware');

/**
 * Save logged In User
 */
$app->add('App\Middleware\UserMiddleware');

/**
 * Save Base URL for Links in E-Mail
 */
$app->add('App\Middleware\BaseURLMiddleware');