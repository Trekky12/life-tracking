<?php

use Slim\App;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteParser;

return function (App $app) {

    $app->group('', function(RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Main\FrontpageAction::class)->setName('index');
        $group->get('/login', \App\Application\Action\Main\LoginpageAction::class)->setName('login');
        $group->post('/login', \App\Application\Action\Main\LoginAction::class)->setName('login');
        $group->get('/logout', \App\Application\Action\Main\LogoutAction::class)->setName('logout');

        $group->get('/cron', \App\Application\Action\Main\CronAction::class)->setName('cron');

        $group->get('/logfile', \App\Application\Action\Main\LogfileAction::class)->setName('logfile')->add('App\Application\Middleware\AdminMiddleware');

        $group->post('/tokens', \App\Application\Action\Main\CSRFTokensAction::class)->setName('get_csrf_tokens');

        $group->group('/banlist', function(RouteCollectorProxy $group_banlist) {
            $group_banlist->get('/', \App\Application\Action\Admin\BanlistAction::class)->setName('banlist');
            $group_banlist->delete('/deleteIP/{ip}', \App\Application\Action\Admin\BanlistDeleteAction::class)->setName('banlist_delete');
        })->add(\App\Application\Middleware\AdminMiddleware::class);
    });

    $app->group('/finances', function(RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Finances\FinancesListAction::class)->setName('finances');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\FinancesEditAction::class)->setName('finances_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\FinancesSaveAction::class)->setName('finances_save');
        $group->delete('/delete/{id}', \App\Application\Action\Finances\FinancesDeleteAction::class)->setName('finances_delete');

        $group->get('/table/', \App\Application\Action\Finances\FinancesTableAction::class)->setName('finances_table');

        $group->group('/stats', function(RouteCollectorProxy $group_stats) {
            $group_stats->get('/', \App\Application\Action\Finances\Stats\FinancesStatsAction::class)->setName('finances_stats');
            $group_stats->get('/{year:[0-9]{4}}/categories/{type:[0-1]}', \App\Application\Action\Finances\Stats\FinancesYearCategoryAction::class)->setName('finances_stats_category');
            $group_stats->get('/{year:[0-9]{4}}/categories/{type:[0-1]}/{category:[0-9]+}', \App\Application\Action\Finances\Stats\FinancesYearCategoryDetailAction::class)->setName('finances_stats_category_detail');
            $group_stats->get('/{year:[0-9]{4}}/', \App\Application\Action\Finances\Stats\FinancesYearAction::class)->setName('finances_stats_year');
            $group_stats->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/{type:[0-1]}/', \App\Application\Action\Finances\Stats\FinancesMonthTypeAction::class)->setName('finances_stats_month_type');
            $group_stats->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/{type:[0-1]}/{category:[0-9]+}', \App\Application\Action\Finances\Stats\FinancesMonthTypeCategoryAction::class)->setName('finances_stats_month_category');
            $group_stats->get('/budget/{budget:[0-9]+}', \App\Application\Action\Finances\Stats\FinancesBudgetAction::class)->setName('finances_stats_budget');
        });


        $group->group('/categories', function(RouteCollectorProxy $group_cats) {
            $group_cats->get('/', \App\Application\Action\Finances\Category\CategoryListAction::class)->setName('finances_categories');
            $group_cats->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Category\CategoryEditAction::class)->setName('finances_categories_edit');
            $group_cats->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Category\CategorySaveAction::class)->setName('finances_categories_save');
            $group_cats->delete('/delete/{id}', \App\Application\Action\Finances\Category\CategoryDeleteAction::class)->setName('finances_categories_delete');

            $group_cats->group('/assignment', function(RouteCollectorProxy $group_assignments) {
                $group_assignments->get('/', \App\Application\Action\Finances\Assignment\AssignmentListAction::class)->setName('finances_categories_assignment');
                $group_assignments->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Assignment\AssignmentEditAction::class)->setName('finances_categories_assignment_edit');
                $group_assignments->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Assignment\AssignmentSaveAction::class)->setName('finances_categories_assignment_save');
                $group_assignments->delete('/delete/{id}', \App\Application\Action\Finances\Assignment\AssignmentDeleteAction::class)->setName('finances_categories_assignment_delete');
            });
        });

        $group->group('/budgets', function(RouteCollectorProxy $group_budgets) {
            $group_budgets->get('/', \App\Application\Action\Finances\Budget\BudgetListAction::class)->setName('finances_budgets');
            $group_budgets->get('/edit/', \App\Application\Action\Finances\Budget\BudgetEditAction::class)->setName('finances_budgets_edit');
            $group_budgets->post('/saveAll', \App\Application\Action\Finances\Budget\BudgetSaveAction::class)->setName('finances_budgets_save_all');
            $group_budgets->delete('/delete/{id}', \App\Application\Action\Finances\Budget\BudgetDeleteAction::class)->setName('finances_budgets_delete');

            $group_budgets->get('/costs/', \App\Application\Action\Finances\Budget\BudgetCategoryCostsAction::class)->setName('finances_budgets_category_costs');
        });

        $group->group('/recurring', function(RouteCollectorProxy $group_recurring) {
            $group_recurring->get('/', \App\Application\Action\Finances\Recurring\RecurringListAction::class)->setName('finances_recurring');
            $group_recurring->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Recurring\RecurringEditAction::class)->setName('finances_recurring_edit');
            $group_recurring->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Recurring\RecurringSaveAction::class)->setName('finances_recurring_save');
            $group_recurring->delete('/delete/{id}', \App\Application\Action\Finances\Recurring\RecurringDeleteAction::class)->setName('finances_recurring_delete');
        });

        $group->group('/methods', function(RouteCollectorProxy $group_methods) {
            $group_methods->get('/', \App\Application\Action\Finances\Paymethod\PaymethodListAction::class)->setName('finances_paymethod');
            $group_methods->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Paymethod\PaymethodEditAction::class)->setName('finances_paymethod_edit');
            $group_methods->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Paymethod\PaymethodSaveAction::class)->setName('finances_paymethod_save');
            $group_methods->delete('/delete/{id}', \App\Application\Action\Finances\Paymethod\PaymethodDeleteAction::class)->setName('finances_paymethod_delete');
        });
    });

    $app->group('/location', function(RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Location\LocationMapAction::class)->setName('location');
        $group->post('/record', \App\Application\Action\Location\LocationRecordAction::class)->setName('location_record');
        $group->get('/markers', \App\Application\Action\Location\LocationMarkersAction::class)->setName('getMarkers');
        $group->delete('/delete/[{id}]', \App\Application\Action\Location\LocationDeleteAction::class)->setName('delete_marker');
        $group->get('/address/[{id}]', \App\Application\Action\Location\LocationAddressAction::class)->setName('get_address');

        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Location\LocationEditAction::class)->setName('location_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Location\LocationSaveAction::class)->setName('location_save');

        $group->group('/steps', function(RouteCollectorProxy $group_steps) {
            $group_steps->get('/', \App\Application\Action\Location\Steps\StepsAction::class)->setName('steps');
            $group_steps->get('/{year:[0-9]{4}}/', \App\Application\Action\Location\Steps\StepsYearAction::class)->setName('steps_stats_year');
            $group_steps->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/', \App\Application\Action\Location\Steps\StepsYearMonthAction::class)->setName('steps_stats_month');

            $group_steps->group('/{date:[0-9]{4}-[0-9]{2}-[0-9]{2}}', function(RouteCollectorProxy $group_steps_date) {
                $group_steps_date->get('/edit/', \App\Application\Action\Location\Steps\StepsEditAction::class)->setName('steps_day_edit');
                $group_steps_date->post('/save/', \App\Application\Action\Location\Steps\StepsSaveAction::class)->setName('steps_save');
            });
        });
    });

    $app->group('/cars', function(RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('car_service'))->withStatus(302);
        });

        $group->group('/service', function(RouteCollectorProxy $group_service) {
            $group_service->get('/', \App\Application\Action\Car\Service\ServiceListAction::class)->setName('car_service');
            $group_service->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Car\Service\ServiceEditAction::class)->setName('car_service_edit');
            $group_service->post('/save/[{id:[0-9]+}]', \App\Application\Action\Car\Service\ServiceSaveAction::class)->setName('car_service_save');
            $group_service->delete('/delete/{id}', \App\Application\Action\Car\Service\ServiceDeleteAction::class)->setName('car_service_delete');

            $group_service->get('/table/fuel/', \App\Application\Action\Car\Service\FuelTableAction::class)->setName('car_service_fuel_table');
            $group_service->get('/table/service/', \App\Application\Action\Car\Service\ServiceTableAction::class)->setName('car_service_service_table');
            $group_service->get('/stats/', \App\Application\Action\Car\Stats\CarServiceStatsAction::class)->setName('car_service_stats');
            $group_service->post('/setYearlyMileageCalcTyp', \App\Application\Action\Car\Stats\CalculationTypeAction::class)->setName('set_mileage_type');
        });

        $group->group('/control', function(RouteCollectorProxy $group_control) {
            $group_control->get('/', \App\Application\Action\Car\Car\CarListAction::class)->setName('cars');
            $group_control->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Car\Car\CarEditAction::class)->setName('cars_edit');
            $group_control->post('/save/[{id:[0-9]+}]', \App\Application\Action\Car\Car\CarSaveAction::class)->setName('cars_save');
            $group_control->delete('/delete/{id}', \App\Application\Action\Car\Car\CarDeleteAction::class)->setName('cars_delete');
        })->add(\App\Application\Middleware\AdminMiddleware::class);
    });


    $app->group('/profile', function(RouteCollectorProxy $group) {
        $group->get('/changepassword', \App\Application\Action\Profile\ChangePasswordpageAction::class)->setName('users_change_password');
        $group->post('/changepassword', \App\Application\Action\Profile\ChangePasswordAction::class)->setName('users_change_password');
        $group->get('/image', \App\Application\Action\Profile\ProfileImageAction::class)->setName('users_profile_image');
        $group->post('/image', \App\Application\Action\Profile\ProfileImageSaveAction::class)->setName('users_profile_image');

        $group->get('/edit', \App\Application\Action\Profile\ProfileEditAction::class)->setName('users_profile_edit');
        $group->post('/edit', \App\Application\Action\Profile\ProfileSaveAction::class)->setName('users_profile_edit');

        $group->group('/favorites', function(RouteCollectorProxy $group_favorites) {
            $group_favorites->get('/', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesListAction::class)->setName('users_mobile_favorites');
            $group_favorites->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesEditAction::class)->setName('users_mobile_favorites_edit');
            $group_favorites->post('/save/[{id:[0-9]+}]', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesSaveAction::class)->setName('users_mobile_favorites_save');
            $group_favorites->delete('/delete/{id}', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesDeleteAction::class)->setName('users_mobile_favorites_delete');
        });

        $group->group('/tokens', function(RouteCollectorProxy $group_tokens) {
            $group_tokens->get('/', \App\Application\Action\Profile\LoginTokensListAction::class)->setName('users_login_tokens');
            $group_tokens->delete('/delete/{id}', \App\Application\Action\Profile\LoginTokensDeleteAction::class)->setName('users_login_tokens_delete');
        });

        $group->get('/activity', \App\Application\Action\Activity\ActivityAction::class)->setName('users_activities');
        $group->post('/getActivities', \App\Application\Action\Activity\ActivityListAction::class)->setName('activities_get');
    });

    $app->group('/users', function(RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\User\UserListAction::class)->setName('users');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\User\UserEditAction::class)->setName('users_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\User\UserSaveAction::class)->setName('users_save');
        $group->delete('/delete/{id}', \App\Application\Action\User\UserDeleteAction::class)->setName('users_delete');

        $group->group('/tokens', function(RouteCollectorProxy $group_tokens) {
            $group_tokens->get('/', \App\Application\Action\User\LoginTokens\LoginTokensListAction::class)->setName('login_tokens');
            $group_tokens->delete('/delete/{id}', \App\Application\Action\User\LoginTokens\LoginTokensDeleteAction::class)->setName('login_tokens_delete');
            $group_tokens->get('/deleteOld',  \App\Application\Action\User\LoginTokens\LoginTokensDeleteOldAction::class)->setName('login_tokens_delete_old');
        });

        $group->group('/{user:[0-9]+}', function(RouteCollectorProxy $group_user) {

            $group_user->get('/testmail', \App\Application\Action\User\TestMailAction::class)->setName('users_test_mail');

            $group_user->group('/favorites', function(RouteCollectorProxy $group_user_favorites) {
                $group_user_favorites->get('/', \App\Application\Action\User\MobileFavorites\MobileFavoritesListAction::class)->setName('users_mobile_favorites_admin');
                $group_user_favorites->get('/edit/[{id:[0-9]+}]', \App\Application\Action\User\MobileFavorites\MobileFavoritesEditAction::class)->setName('users_mobile_favorites_edit_admin');
                $group_user_favorites->post('/save/[{id:[0-9]+}]', \App\Application\Action\User\MobileFavorites\MobileFavoritesSaveAction::class)->setName('users_mobile_favorites_save_admin');
                $group_user_favorites->delete('/delete/{id}', \App\Application\Action\User\MobileFavorites\MobileFavoritesDeleteAction::class)->setName('users_mobile_favorites_delete_admin');
            });
        });
    })->add(\App\Application\Middleware\AdminMiddleware::class);


    $app->group('/notifications', function(RouteCollectorProxy $group) {

        // https://github.com/slimphp/Slim/pull/2776
        $group->group('/clients', function(RouteCollectorProxy $group_clients) {
            $group_clients->get('/', '\App\Domain\Notifications\Clients\Controller:index')->setName('notifications_clients');
            $group_clients->delete('/delete/{id}', '\App\Domain\Notifications\Clients\Controller:delete')->setName('notifications_clients_delete');
            $group_clients->map(['GET', 'POST'], '/test/{id:[0-9]+}', '\App\Domain\Notifications\Controller:testNotification')->setName('notifications_clients_test');
        })->add('App\Application\Middleware\AdminMiddleware');

        $group->group('/categories', function(RouteCollectorProxy $group_categories) {
            $group_categories->get('/', '\App\Domain\Notifications\Categories\Controller:index')->setName('notifications_categories');
            $group_categories->get('/edit/[{id:[0-9]+}]', '\App\Domain\Notifications\Categories\Controller:edit')->setName('notifications_categories_edit');
            $group_categories->post('/save/[{id:[0-9]+}]', '\App\Domain\Notifications\Categories\Controller:save')->setName('notifications_categories_save');
            $group_categories->delete('/delete/{id}', '\App\Domain\Notifications\Categories\Controller:delete')->setName('notifications_categories_delete');
        })->add('App\Application\Middleware\AdminMiddleware');

        $group->get('/', '\App\Domain\Notifications\Controller:overview')->setName('notifications');
        $group->get('/manage/', '\App\Domain\Notifications\Controller:manage')->setName('notifications_clients_manage');
        $group->map(['POST', 'PUT', 'DELETE'], '/subscribe/', '\App\Domain\Notifications\Clients\Controller:subscribe')->setName('notifications_clients_subscribe');

        $group->get('/notify', '\App\Domain\Notifications\Controller:notifyByCategory');
        // use post because endpoint param is too complex for a GET param
        $group->post('/getCategories', '\App\Domain\Notifications\Clients\Controller:getCategoriesFromEndpoint')->setName('notifications_clients_categories');
        $group->post('/setCategorySubscription', '\App\Domain\Notifications\Clients\Controller:setCategoryOfEndpoint')->setName('notifications_clients_set_category');
        $group->post('/setCategoryUser', '\App\Domain\Notifications\Users\Controller:setCategoryforUser')->setName('notifications_clients_set_category_user');

        $group->post('/getNotifications', '\App\Domain\Notifications\Controller:getNotificationsByUser')->setName('notifications_get');
        $group->post('/getUnreadNotifications', '\App\Domain\Notifications\Controller:getUnreadNotificationsByUser')->setName('notifications_get_unread');
    });


    $app->group('/boards', function(RouteCollectorProxy $group) {
        $group->get('/', '\App\Domain\Board\Controller:index')->setName('boards');
        $group->get('/edit/[{id:[0-9]+}]', '\App\Domain\Board\Controller:edit')->setName('boards_edit');
        $group->post('/save/[{id:[0-9]+}]', '\App\Domain\Board\Controller:save')->setName('boards_save');
        $group->delete('/delete/{id}', '\App\Domain\Board\Controller:delete')->setName('boards_delete');

        $group->group('/view', function(RouteCollectorProxy $group_view) {
            $group_view->get('/{hash}', '\App\Domain\Board\Controller:view')->setName('boards_view');
        });

        $group->group('/stacks', function(RouteCollectorProxy $group_stacks) {
            $group_stacks->post('/save/[{id:[0-9]+}]', '\App\Domain\Board\Stack\Controller:saveAPI')->setName('stack_save');
            $group_stacks->post('/updatePosition', '\App\Domain\Board\Stack\Controller:updatePosition')->setName('stack_update_position');
            $group_stacks->delete('/delete/[{id:[0-9]+}]', '\App\Domain\Board\Stack\Controller:delete')->setName('stack_delete');
            $group_stacks->post('/archive/[{id:[0-9]+}]', '\App\Domain\Board\Stack\Controller:archive')->setName('stack_archive');
            $group_stacks->get('/data/[{id:[0-9]+}]', '\App\Domain\Board\Stack\Controller:getAPI')->setName('stack_get');
        });
        $group->group('/card', function(RouteCollectorProxy $group_cards) {
            $group_cards->post('/save/[{id:[0-9]+}]', '\App\Domain\Board\Card\Controller:saveAPI')->setName('card_save');
            $group_cards->post('/updatePosition', '\App\Domain\Board\Card\Controller:updatePosition')->setName('card_update_position');
            $group_cards->post('/moveCard', '\App\Domain\Board\Card\Controller:moveCard')->setName('card_move_stack');
            $group_cards->get('/data/[{id:[0-9]+}]', '\App\Domain\Board\Card\Controller:getAPI')->setName('card_get');
            $group_cards->delete('/delete/[{id:[0-9]+}]', '\App\Domain\Board\Card\Controller:delete')->setName('card_delete');
            $group_cards->post('/archive/[{id:[0-9]+}]', '\App\Domain\Board\Card\Controller:archive')->setName('card_archive');
        });

        $group->group('/labels', function(RouteCollectorProxy $group_labels) {
            $group_labels->post('/save/[{id:[0-9]+}]', '\App\Domain\Board\Label\Controller:saveAPI')->setName('label_save');
            $group_labels->delete('/delete/[{id:[0-9]+}]', '\App\Domain\Board\Label\Controller:delete')->setName('label_delete');
            $group_labels->get('/data/[{id:[0-9]+}]', '\App\Domain\Board\Label\Controller:getAPI')->setName('label_get');
        });

        $group->post('/setArchive', '\App\Domain\Board\Controller:setArchive')->setName('set_archive');
    });

    $app->group('/crawlers', function(RouteCollectorProxy $group) {
        $group->get('/', '\App\Domain\Crawler\Controller:index')->setName('crawlers');
        $group->get('/edit/[{id:[0-9]+}]', '\App\Domain\Crawler\Controller:edit')->setName('crawlers_edit');
        $group->post('/save/[{id:[0-9]+}]', '\App\Domain\Crawler\Controller:save')->setName('crawlers_save');
        $group->delete('/delete/{id}', '\App\Domain\Crawler\Controller:delete')->setName('crawlers_delete');


        $group->group('/{crawler}', function(RouteCollectorProxy $group_crawler) {

            $group_crawler->get('/view/', '\App\Domain\Crawler\Controller:view')->setName('crawlers_view');
            $group_crawler->get('/table/', '\App\Domain\Crawler\Controller:table')->setName('crawlers_table');
            $group_crawler->post('/setFilter/', '\App\Domain\Crawler\Controller:setFilter')->setName('set_crawler_filter');

            $group_crawler->post('/record/', '\App\Domain\Crawler\CrawlerDataset\Controller:record')->setName('crawler_record');

            $group_crawler->group('/headers', function(RouteCollectorProxy $group_header) {
                $group_header->get('/', '\App\Domain\Crawler\CrawlerHeader\Controller:index')->setName('crawlers_headers');
                $group_header->get('/edit/[{id:[0-9]+}]', '\App\Domain\Crawler\CrawlerHeader\Controller:edit')->setName('crawlers_headers_edit');
                $group_header->post('/save/[{id:[0-9]+}]', '\App\Domain\Crawler\CrawlerHeader\Controller:save')->setName('crawlers_headers_save');
                $group_header->delete('/delete/{id}', '\App\Domain\Crawler\CrawlerHeader\Controller:delete')->setName('crawlers_headers_delete');

                $group_header->get('/clone/', '\App\Domain\Crawler\CrawlerHeader\Controller:clone')->setName('crawlers_headers_clone');
                $group_header->post('/cloning/', '\App\Domain\Crawler\CrawlerHeader\Controller:cloning')->setName('crawlers_headers_cloning');
            });

            $group_crawler->group('/links', function(RouteCollectorProxy $group_links) {
                $group_links->get('/', '\App\Domain\Crawler\CrawlerLink\Controller:index')->setName('crawlers_links');
                $group_links->get('/edit/[{id:[0-9]+}]', '\App\Domain\Crawler\CrawlerLink\Controller:edit')->setName('crawlers_links_edit');
                $group_links->post('/save/[{id:[0-9]+}]', '\App\Domain\Crawler\CrawlerLink\Controller:save')->setName('crawlers_links_save');
                $group_links->delete('/delete/{id}', '\App\Domain\Crawler\CrawlerLink\Controller:delete')->setName('crawlers_links_delete');
            });
        });
    });

    $app->group('/splitbills', function(RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('splitbills'))->withStatus(302);
        });

        $group->group('/groups', function(RouteCollectorProxy $group_groups) {
            $group_groups->get('/', '\App\Domain\Splitbill\Group\Controller:index')->setName('splitbills');
            $group_groups->get('/edit/[{id:[0-9]+}]', '\App\Domain\Splitbill\Group\Controller:edit')->setName('splitbill_groups_edit');
            $group_groups->post('/save/[{id:[0-9]+}]', '\App\Domain\Splitbill\Group\Controller:save')->setName('splitbill_groups_save');
            $group_groups->delete('/delete/{id}', '\App\Domain\Splitbill\Group\Controller:delete')->setName('splitbill_groups_delete');
        });

        $group->group('/{group}', function(RouteCollectorProxy $group_group) {

            $group_group->get('/view/', '\App\Domain\Splitbill\Bill\Controller:index')->setName('splitbill_bills');
            $group_group->get('/table/', '\App\Domain\Splitbill\Bill\Controller:table')->setName('splitbill_bills_table');

            $group_group->group('/bills', function(RouteCollectorProxy $group_bill) {
                $group_bill->get('/edit/[{id:[0-9]+}]', '\App\Domain\Splitbill\Bill\Controller:edit')->setName('splitbill_bills_edit');
                $group_bill->post('/save/[{id:[0-9]+}]', '\App\Domain\Splitbill\Bill\Controller:save')->setName('splitbill_bills_save');
                $group_bill->delete('/delete/{id}', '\App\Domain\Splitbill\Bill\Controller:delete')->setName('splitbill_bills_delete');
            });
        });
    });

    $app->group('/trips', function(RouteCollectorProxy $group) {
        $group->get('/', '\App\Domain\Trips\Controller:index')->setName('trips');
        $group->get('/edit/[{id:[0-9]+}]', '\App\Domain\Trips\Controller:edit')->setName('trips_edit');
        $group->post('/save/[{id:[0-9]+}]', '\App\Domain\Trips\Controller:save')->setName('trips_save');
        $group->delete('/delete/{id}', '\App\Domain\Trips\Controller:delete')->setName('trips_delete');

        $group->get('/search/', '\App\Domain\Trips\Event\Controller:getLatLng')->setName('get_location_of_address');

        $group->group('/{trip}', function(RouteCollectorProxy $group_trip) {

            $group_trip->get('/view/', '\App\Domain\Trips\Event\Controller:index')->setName('trips_view');
            $group_trip->get('/markers/', '\App\Domain\Trips\Event\Controller:getMarkers')->setName('trips_markers');

            $group_trip->group('/event', function(RouteCollectorProxy $group_event) {
                $group_event->get('/edit/[{id:[0-9]+}]', '\App\Domain\Trips\Event\Controller:edit')->setName('trips_event_edit');
                $group_event->post('/save/[{id:[0-9]+}]', '\App\Domain\Trips\Event\Controller:save')->setName('trips_event_save');
                $group_event->delete('/delete/{id}', '\App\Domain\Trips\Event\Controller:delete')->setName('trips_event_delete');

                $group_event->post('/image/{id:[0-9]+}', '\App\Domain\Trips\Event\Controller:image')->setName('trips_event_image');
                $group_event->delete('/imagedelete/{id:[0-9]+}', '\App\Domain\Trips\Event\Controller:image_delete')->setName('trips_event_image_delete');

                $group_event->post('/updatePosition', '\App\Domain\Trips\Event\Controller:updatePosition')->setName('trips_event_position');
            });
        });
    });

    $app->group('/timesheets', function(RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('timesheets'))->withStatus(302);
        });

        $group->group('/projects', function(RouteCollectorProxy $group_projects) {
            $group_projects->get('/', '\App\Domain\Timesheets\Project\Controller:index')->setName('timesheets');
            $group_projects->get('/edit/[{id:[0-9]+}]', '\App\Domain\Timesheets\Project\Controller:edit')->setName('timesheets_projects_edit');
            $group_projects->post('/save/[{id:[0-9]+}]', '\App\Domain\Timesheets\Project\Controller:save')->setName('timesheets_projects_save');
            $group_projects->delete('/delete/{id}', '\App\Domain\Timesheets\Project\Controller:delete')->setName('timesheets_projects_delete');
        });

        $group->group('/{project}', function(RouteCollectorProxy $group_project) {

            $group_project->get('/view/', '\App\Domain\Timesheets\Sheet\Controller:index')->setName('timesheets_sheets');
            $group_project->get('/table/', '\App\Domain\Timesheets\Sheet\Controller:table')->setName('timesheets_sheets_table');

            $group_project->group('/sheets', function(RouteCollectorProxy $group_sheets) {
                $group_sheets->get('/edit/[{id:[0-9]+}]', '\App\Domain\Timesheets\Sheet\Controller:edit')->setName('timesheets_sheets_edit');
                $group_sheets->post('/save/[{id:[0-9]+}]', '\App\Domain\Timesheets\Sheet\Controller:save')->setName('timesheets_sheets_save');
                $group_sheets->delete('/delete/{id}', '\App\Domain\Timesheets\Sheet\Controller:delete')->setName('timesheets_sheets_delete');
            });

            $group_project->group('/fast', function(RouteCollectorProxy $group_fast) {
                $group_fast->get('/', '\App\Domain\Timesheets\Sheet\Controller:showfastCheckInCheckOut')->setName('timesheets_fast');
                $group_fast->post('/checkin', '\App\Domain\Timesheets\Sheet\Controller:fastCheckIn')->setName('timesheets_fast_checkin');
                $group_fast->post('/checkout', '\App\Domain\Timesheets\Sheet\Controller:fastCheckOut')->setName('timesheets_fast_checkout');
            });

            $group_project->get('/export', '\App\Domain\Timesheets\Sheet\Controller:export')->setName('timesheets_export');
        });
    });
};
