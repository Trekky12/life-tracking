<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('', function() {
    $this->get('/', '\App\Main\MainController:index')->setName('index');
    $this->map(['GET', 'POST'], '/login', '\App\Main\MainController:login')->setName('login');
    $this->get('/logout', '\App\Main\MainController:logout')->setName('logout');

    $this->get('/dataTable', '\App\Main\MainController:getDatatableLang')->setName('datatable_lang');

    $this->get('/cron', '\App\Main\MainController:cron')->setName('cron');
    
    $this->get('/logfile', '\App\Main\MainController:showLog')->setName('logfile')->add('App\Middleware\AdminMiddleware');
});

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
        $this->get('/budget/{budget:[0-9]+}', '\App\Finances\Controller:statsBudget')->setName('finances_stats_budget');
    });


    $this->group('/categories', function() {
        $this->get('/', '\App\Finances\Category\Controller:index')->setName('finances_categories');
        $this->get('/edit/[{id:[0-9]+}]', '\App\Finances\Category\Controller:edit')->setName('finances_categories_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\Finances\Category\Controller:save')->setName('finances_categories_save');
        $this->delete('/delete/{id}', '\App\Finances\Category\Controller:delete')->setName('finances_categories_delete');

        $this->group('/assignment', function() {
            $this->get('/', '\App\Finances\Assignment\Controller:index')->setName('finances_categories_assignment');
            $this->get('/edit/[{id:[0-9]+}]', '\App\Finances\Assignment\Controller:edit')->setName('finances_categories_assignment_edit');
            $this->post('/save/[{id:[0-9]+}]', '\App\Finances\Assignment\Controller:save')->setName('finances_categories_assignment_save');
            $this->delete('/delete/{id}', '\App\Finances\Assignment\Controller:delete')->setName('finances_categories_assignment_delete');
        });
    });

    $this->group('/budgets', function() {
        $this->get('/', '\App\Finances\Budget\Controller:index')->setName('finances_budgets');
        $this->get('/edit/', '\App\Finances\Budget\Controller:edit')->setName('finances_budgets_edit');
        //$this->post('/save/[{id:[0-9]+}]', '\App\Finances\Budget\Controller:save')->setName('finances_budgets_save');
        $this->post('/saveAll', '\App\Finances\Budget\Controller:saveAll')->setName('finances_budgets_save_all');
        $this->delete('/delete/{id}', '\App\Finances\Budget\Controller:delete')->setName('finances_budgets_delete');

        $this->get('/costs/', '\App\Finances\Budget\Controller:getCategoryCosts')->setName('finances_budgets_category_costs');
    });

    $this->group('/recurring', function() {
        $this->get('/', '\App\Finances\Recurring\Controller:index')->setName('finances_recurring');
        $this->get('/edit/[{id:[0-9]+}]', '\App\Finances\Recurring\Controller:edit')->setName('finances_recurring_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\Finances\Recurring\Controller:save')->setName('finances_recurring_save');
        $this->delete('/delete/{id}', '\App\Finances\Recurring\Controller:delete')->setName('finances_recurring_delete');
    });
});

$app->group('/location', function() {
    $this->get('/', '\App\Location\Controller:index')->setName('location');
    $this->post('/record', '\App\Location\Controller:saveAPI')->setName('record');
    $this->get('/markers', '\App\Location\Controller:getMarkers')->setName('getMarkers');
    $this->delete('/delete/[{id}]', '\App\Location\Controller:delete')->setName('delete_marker');
    $this->get('/address/[{id}]', '\App\Location\Controller:getAddress')->setName('get_address');
});

$app->group('/cars', function() {

    $this->get('/', function (Request $request, Response $response) {
        return $response->withRedirect($this->get('router')->pathFor('car_service'), 302);
    });

    $this->group('/service', function() {
        $this->get('/', '\App\Car\Service\Controller:index')->setName('car_service');
        $this->get('/edit/[{id:[0-9]+}]', '\App\Car\Service\Controller:edit')->setName('car_service_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\Car\Service\Controller:save')->setName('car_service_save');
        $this->delete('/delete/{id}', '\App\Car\Service\Controller:delete')->setName('car_service_delete');

        $this->get('/table/fuel/', '\App\Car\Service\Controller:tableFuel')->setName('car_service_fuel_table');
        $this->get('/table/service/', '\App\Car\Service\Controller:tableService')->setName('car_service_service_table');
        $this->get('/stats/', '\App\Car\Service\Controller:stats')->setName('car_service_stats');
        $this->post('/setYearlyMileageCalcTyp', '\App\Car\Service\Controller:setYearlyMileageCalcTyp')->setName('set_mileage_type');
    });

    $this->group('/control', function() {
        $this->get('/', '\App\Car\Controller:index')->setName('cars');
        $this->get('/edit/[{id:[0-9]+}]', '\App\Car\Controller:edit')->setName('cars_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\Car\Controller:save')->setName('cars_save');
        $this->delete('/delete/{id}', '\App\Car\Controller:delete')->setName('cars_delete');
    })->add('App\Middleware\AdminMiddleware');
});


$app->group('/profile', function() {
    $this->map(['GET', 'POST'], '/changepassword', '\App\User\Controller:changePassword')->setName('users_change_password');
    $this->map(['GET', 'POST'], '/image', '\App\User\Controller:setProfileImage')->setName('users_profile_image');
});

$app->group('/users', function() {
    $this->get('/', '\App\User\Controller:index')->setName('users');
    $this->get('/edit/[{id:[0-9]+}]', '\App\User\Controller:edit')->setName('users_edit');
    $this->post('/save/[{id:[0-9]+}]', '\App\User\Controller:save')->setName('users_save');
    $this->delete('/delete/{id}', '\App\User\Controller:delete')->setName('users_delete');

    $this->get('/testmail/{id:[0-9]+}', '\App\User\Controller:testMail')->setName('users_test_mail');
})->add('App\Middleware\AdminMiddleware');



$app->group('/boards', function() {
    $this->get('/', '\App\Board\Controller:index')->setName('boards');
    $this->get('/edit/[{id:[0-9]+}]', '\App\Board\Controller:edit')->setName('boards_edit');
    $this->post('/save/[{id:[0-9]+}]', '\App\Board\Controller:save')->setName('boards_save');
    $this->delete('/delete/{id}', '\App\Board\Controller:delete')->setName('boards_delete');

    $this->group('/view', function() {
        $this->get('/{hash}', '\App\Board\Controller:view')->setName('boards_view');
    });

    $this->group('/stacks', function() {
        $this->post('/save/[{id:[0-9]+}]', '\App\Board\Stack\Controller:saveAPI')->setName('stack_save');
        $this->post('/updatePosition', '\App\Board\Stack\Controller:updatePosition')->setName('stack_update_position');
        $this->delete('/delete/[{id:[0-9]+}]', '\App\Board\Stack\Controller:delete')->setName('stack_delete');
        $this->post('/archive/[{id:[0-9]+}]', '\App\Board\Stack\Controller:archive')->setName('stack_archive');
        $this->get('/data/[{id:[0-9]+}]', '\App\Board\Stack\Controller:getAPI')->setName('stack_get');
    });
    $this->group('/card', function() {
        $this->post('/save/[{id:[0-9]+}]', '\App\Board\Card\Controller:saveAPI')->setName('card_save');
        $this->post('/updatePosition', '\App\Board\Card\Controller:updatePosition')->setName('card_update_position');
        $this->post('/moveCard', '\App\Board\Card\Controller:moveCard')->setName('card_move_stack');
        $this->get('/data/[{id:[0-9]+}]', '\App\Board\Card\Controller:getAPI')->setName('card_get');
        $this->delete('/delete/[{id:[0-9]+}]', '\App\Board\Card\Controller:delete')->setName('card_delete');
        $this->post('/archive/[{id:[0-9]+}]', '\App\Board\Card\Controller:archive')->setName('card_archive');

        $this->post('/saveComment/[{id:[0-9]+}]', '\App\Board\Comment\Controller:saveAPI')->setName('comment_save');
    });

    $this->group('/labels', function() {
        $this->post('/save/[{id:[0-9]+}]', '\App\Board\Label\Controller:saveAPI')->setName('label_save');
        $this->delete('/delete/[{id:[0-9]+}]', '\App\Board\Label\Controller:delete')->setName('label_delete');
        $this->get('/data/[{id:[0-9]+}]', '\App\Board\Label\Controller:getAPI')->setName('label_get');
    });

    $this->post('/setArchive', '\App\Board\Controller:setArchive')->setName('set_archive');
});
