<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
    return $response->withRedirect($this->get('router')->pathFor('finances'), 302);
})->setName('index');

$app->group('/finances', function() {
    $this->get('/', '\App\Finances\Controller:index')->setName('finances');
    $this->get('/edit/[{id:[0-9]+}]', '\App\Finances\Controller:edit')->setName('finances_edit');
    $this->post('/save/[{id:[0-9]+}]', '\App\Finances\Controller:save')->setName('finances_save');
    $this->delete('/delete/{id}', '\App\Finances\Controller:delete')->setName('finances_delete');

    $this->post('/record/', '\App\Finances\Controller:record');
    
    $this->get('/table/', '\App\Finances\Controller:table')->setName('finances_table');

    $this->group('/stats', function() {
        $this->get('/', '\App\Finances\Controller:stats')->setName('finances_stats');
        $this->get('/{year:[0-9]{4}}/', '\App\Finances\Controller:statsYear')->setName('finances_stats_year');
        $this->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/{type:[0-1]}/', '\App\Finances\Controller:statsMonthType')->setName('finances_stats_month_type');
        $this->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/{type:[0-1]}/{category:[0-9]+}', '\App\Finances\Controller:statsMonthCategory')->setName('finances_stats_month_category');
    });


    $this->group('/categories', function() {
        $this->get('/', '\App\FinancesCategory\Controller:index')->setName('finances_categories');
        $this->get('/edit/[{id:[0-9]+}]', '\App\FinancesCategory\Controller:edit')->setName('finances_categories_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\FinancesCategory\Controller:save')->setName('finances_categories_save');
        $this->delete('/delete/{id}', '\App\FinancesCategory\Controller:delete')->setName('finances_categories_delete');
    });

    $this->group('/monthly', function() {
        $this->get('/', '\App\FinancesMonthly\Controller:index')->setName('finances_monthly');
        $this->get('/edit/[{id:[0-9]+}]', '\App\FinancesMonthly\Controller:edit')->setName('finances_monthly_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\FinancesMonthly\Controller:save')->setName('finances_monthly_save');
        $this->delete('/delete/{id}', '\App\FinancesMonthly\Controller:delete')->setName('finances_monthly_delete');

        $this->get('/update', '\App\FinancesMonthly\Controller:update');
    });
})->add('App\Middleware\ModuleMiddleware');

$app->group('/location', function() {
    $this->get('/', '\App\Location\Controller:index')->setName('location');
    $this->post('/record', '\App\Location\Controller:save')->setName('record');
    $this->get('/markers', '\App\Location\Controller:getMarkers')->setName('getMarkers');
    $this->delete('/delete/[{id}]', '\App\Location\Controller:delete')->setName('delete_marker');
    $this->get('/address/[{id}]', '\App\Location\Controller:getAddress')->setName('get_address');
});

$app->group('/fuel', function() {
    $this->get('/', '\App\Fuel\Controller:index')->setName('fuel');
    $this->get('/edit/[{id:[0-9]+}]', '\App\Fuel\Controller:edit')->setName('fuel_edit');
    $this->post('/save/[{id:[0-9]+}]', '\App\Fuel\Controller:save')->setName('fuel_save');
    $this->delete('/delete/{id}', '\App\Fuel\Controller:delete')->setName('fuel_delete');

    $this->get('/table/', '\App\Fuel\Controller:table')->setName('fuel_table');
    $this->get('/stats/', '\App\Fuel\Controller:stats')->setName('fuel_stats');
    
    $this->group('/cars', function() {
        $this->get('/', '\App\Car\Controller:index')->setName('cars');
        $this->get('/edit/[{id:[0-9]+}]', '\App\Car\Controller:edit')->setName('cars_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\Car\Controller:save')->setName('cars_save');
        $this->delete('/delete/{id}', '\App\Car\Controller:delete')->setName('cars_delete');
    })->add('App\Middleware\AdminMiddleware');
});

$app->get('/dataTable', '\App\Main\MainController:getDatatableLang')->setName('datatable_lang');

$app->map(['GET', 'POST'], '/changepassword', '\App\User\Controller:changePassword')->setName('users_change_password');

$app->group('/users', function() {
    $this->get('/', '\App\User\Controller:index')->setName('users');
    $this->get('/edit/[{id:[0-9]+}]', '\App\User\Controller:edit')->setName('users_edit');
    $this->post('/save/[{id:[0-9]+}]', '\App\User\Controller:save')->setName('users_save');
    $this->delete('/delete/{id}', '\App\User\Controller:delete')->setName('users_delete');

    $this->get('/testmail/{id:[0-9]+}', '\App\User\Controller:testMail')->setName('users_test_mail');
})->add('App\Middleware\AdminMiddleware');

