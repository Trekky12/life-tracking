<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', '\App\Main\MainController:index')->setName('index');

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

        $this->group('/assignment', function() {
            $this->get('/', '\App\FinancesCategoryAssignment\Controller:index')->setName('finances_categories_assignment');
            $this->get('/edit/[{id:[0-9]+}]', '\App\FinancesCategoryAssignment\Controller:edit')->setName('finances_categories_assignment_edit');
            $this->post('/save/[{id:[0-9]+}]', '\App\FinancesCategoryAssignment\Controller:save')->setName('finances_categories_assignment_save');
            $this->delete('/delete/{id}', '\App\FinancesCategoryAssignment\Controller:delete')->setName('finances_categories_assignment_delete');
        });
    });

    $this->group('/monthly', function() {
        $this->get('/', '\App\FinancesMonthly\Controller:index')->setName('finances_monthly');
        $this->get('/edit/[{id:[0-9]+}]', '\App\FinancesMonthly\Controller:edit')->setName('finances_monthly_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\FinancesMonthly\Controller:save')->setName('finances_monthly_save');
        $this->delete('/delete/{id}', '\App\FinancesMonthly\Controller:delete')->setName('finances_monthly_delete');

        $this->get('/update', '\App\FinancesMonthly\Controller:update');
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
        $this->get('/', '\App\CarService\Controller:index')->setName('car_service');
        $this->get('/edit/[{id:[0-9]+}]', '\App\CarService\Controller:edit')->setName('car_service_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\CarService\Controller:save')->setName('car_service_save');
        $this->delete('/delete/{id}', '\App\CarService\Controller:delete')->setName('car_service_delete');

        $this->get('/table/fuel/', '\App\CarService\Controller:tableFuel')->setName('car_service_fuel_table');
        $this->get('/table/service/', '\App\CarService\Controller:tableService')->setName('car_service_service_table');
        $this->get('/stats/', '\App\CarService\Controller:stats')->setName('car_service_stats');
        $this->post('/setYearlyMileageCalcTyp', '\App\CarService\Controller:setYearlyMileageCalcTyp')->setName('set_mileage_type');
    });

    $this->group('/control', function() {
        $this->get('/', '\App\Car\Controller:index')->setName('cars');
        $this->get('/edit/[{id:[0-9]+}]', '\App\Car\Controller:edit')->setName('cars_edit');
        $this->post('/save/[{id:[0-9]+}]', '\App\Car\Controller:save')->setName('cars_save');
        $this->delete('/delete/{id}', '\App\Car\Controller:delete')->setName('cars_delete');
    })->add('App\Middleware\AdminMiddleware');
});

$app->get('/dataTable', '\App\Main\MainController:getDatatableLang')->setName('datatable_lang');

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
    $this->get('/', '\App\Board\BoardController:index')->setName('boards');
    $this->get('/edit/[{id:[0-9]+}]', '\App\Board\BoardController:edit')->setName('boards_edit');
    $this->post('/save/[{id:[0-9]+}]', '\App\Board\BoardController:save')->setName('boards_save');
    $this->delete('/delete/{id}', '\App\Board\BoardController:delete')->setName('boards_delete');

    $this->group('/view', function() {
        $this->get('/{hash}', '\App\Board\BoardController:view')->setName('boards_view');
    });

    $this->group('/stacks', function() {
        $this->post('/save/[{id:[0-9]+}]', '\App\Board\StackController:saveAPI')->setName('stack_save');
        $this->post('/updatePosition', '\App\Board\StackController:updatePosition')->setName('stack_update_position');
        $this->delete('/delete/[{id:[0-9]+}]', '\App\Board\StackController:delete')->setName('stack_delete');
        $this->post('/archive/[{id:[0-9]+}]', '\App\Board\StackController:archive')->setName('stack_archive');
        $this->get('/data/[{id:[0-9]+}]', '\App\Board\StackController:getAPI')->setName('stack_get');
    });
    $this->group('/card', function() {
        $this->post('/save/[{id:[0-9]+}]', '\App\Board\CardController:saveAPI')->setName('card_save');
        $this->post('/updatePosition', '\App\Board\CardController:updatePosition')->setName('card_update_position');
        $this->post('/moveCard', '\App\Board\CardController:moveCard')->setName('card_move_stack');
        $this->get('/data/[{id:[0-9]+}]', '\App\Board\CardController:getAPI')->setName('card_get');
        $this->delete('/delete/[{id:[0-9]+}]', '\App\Board\CardController:delete')->setName('card_delete');
        $this->post('/archive/[{id:[0-9]+}]', '\App\Board\CardController:archive')->setName('card_archive');

        $this->post('/saveComment/[{id:[0-9]+}]', '\App\Board\CommentController:saveAPI')->setName('comment_save');
    });

    $this->group('/labels', function() {
        $this->post('/save/[{id:[0-9]+}]', '\App\Board\LabelController:saveAPI')->setName('label_save');
        $this->delete('/delete/[{id:[0-9]+}]', '\App\Board\LabelController:delete')->setName('label_delete');
        $this->get('/data/[{id:[0-9]+}]', '\App\Board\LabelController:getAPI')->setName('label_get');
    });

    $this->post('/setArchive', '\App\Board\BoardController:setArchive')->setName('set_archive');

    $this->get('/reminder', '\App\Board\CardController:reminder');
});
