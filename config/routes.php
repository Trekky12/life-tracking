<?php

use Slim\App;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteParser;

return function (App $app) {

    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/pwa', \App\Application\Action\Main\PWAFrontpageAction::class)->setName('pwa');
        
        $group->get('/', \App\Application\Action\Main\FrontpageAction::class)->setName('index');
        $group->get('/login', \App\Application\Action\Main\LoginpageAction::class)->setName('login');
        $group->post('/login', \App\Application\Action\Main\LoginAction::class)->setName('login');
        $group->get('/logout', \App\Application\Action\Main\LogoutAction::class)->setName('logout');

        $group->get('/cron', \App\Application\Action\Main\CronAction::class)->setName('cron');
        $group->get('/help', \App\Application\Action\Main\HelpAction::class)->setName('help');

        $group->group('/logfile', function (RouteCollectorProxy $group_logfile) {
            $group_logfile->get('', \App\Application\Action\Main\LogfileAction::class)->setName('logfile');
            $group_logfile->get('/data', \App\Application\Action\Main\LogfileDataAction::class)->setName('logfile_get');
        })->add('App\Application\Middleware\AdminMiddleware');

        $group->post('/tokens', \App\Application\Action\Main\CSRFTokensAction::class)->setName('get_csrf_tokens');

        $group->group('/banlist', function (RouteCollectorProxy $group_banlist) {
            $group_banlist->get('/', \App\Application\Action\Admin\BanlistAction::class)->setName('banlist');
            $group_banlist->delete('/deleteIP/{ip}', \App\Application\Action\Admin\BanlistDeleteAction::class)->setName('banlist_delete');
        })->add(\App\Application\Middleware\AdminMiddleware::class);
    });

    $app->group('/finances', function (RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Finances\FinancesListAction::class)->setName('finances');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\FinancesEditAction::class)->setName('finances_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\FinancesSaveAction::class)->setName('finances_save');
        $group->delete('/delete/{id}', \App\Application\Action\Finances\FinancesDeleteAction::class)->setName('finances_delete');

        $group->get('/table/', \App\Application\Action\Finances\FinancesTableAction::class)->setName('finances_table');

        $group->group('/stats', function (RouteCollectorProxy $group_stats) {
            $group_stats->get('/', \App\Application\Action\Finances\Stats\FinancesStatsAction::class)->setName('finances_stats');
            $group_stats->get('/{year:[0-9]{4}}/categories/{type:[0-1]}', \App\Application\Action\Finances\Stats\FinancesYearCategoryAction::class)->setName('finances_stats_category');
            $group_stats->get('/{year:[0-9]{4}}/categories/{type:[0-1]}/{category:[0-9]+}', \App\Application\Action\Finances\Stats\FinancesYearCategoryDetailAction::class)->setName('finances_stats_category_detail');
            $group_stats->get('/{year:[0-9]{4}}/', \App\Application\Action\Finances\Stats\FinancesYearAction::class)->setName('finances_stats_year');
            $group_stats->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/{type:[0-1]}/', \App\Application\Action\Finances\Stats\FinancesMonthTypeAction::class)->setName('finances_stats_month_type');
            $group_stats->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/{type:[0-1]}/{category:[0-9]+}', \App\Application\Action\Finances\Stats\FinancesMonthTypeCategoryAction::class)->setName('finances_stats_month_category');
            $group_stats->get('/budget/{budget:[0-9]+}', \App\Application\Action\Finances\Stats\FinancesBudgetAction::class)->setName('finances_stats_budget');
        });

        $group->group('/categories', function (RouteCollectorProxy $group_cats) {
            $group_cats->get('/', \App\Application\Action\Finances\Category\CategoryListAction::class)->setName('finances_categories');
            $group_cats->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Category\CategoryEditAction::class)->setName('finances_categories_edit');
            $group_cats->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Category\CategorySaveAction::class)->setName('finances_categories_save');
            $group_cats->delete('/delete/{id}', \App\Application\Action\Finances\Category\CategoryDeleteAction::class)->setName('finances_categories_delete');

            $group_cats->group('/assignment', function (RouteCollectorProxy $group_assignments) {
                $group_assignments->get('/', \App\Application\Action\Finances\Assignment\AssignmentListAction::class)->setName('finances_categories_assignment');
                $group_assignments->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Assignment\AssignmentEditAction::class)->setName('finances_categories_assignment_edit');
                $group_assignments->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Assignment\AssignmentSaveAction::class)->setName('finances_categories_assignment_save');
                $group_assignments->delete('/delete/{id}', \App\Application\Action\Finances\Assignment\AssignmentDeleteAction::class)->setName('finances_categories_assignment_delete');
            });
        });

        $group->group('/budgets', function (RouteCollectorProxy $group_budgets) {
            $group_budgets->get('/', \App\Application\Action\Finances\Budget\BudgetListAction::class)->setName('finances_budgets');
            $group_budgets->get('/edit/', \App\Application\Action\Finances\Budget\BudgetEditAction::class)->setName('finances_budgets_edit');
            $group_budgets->post('/saveAll', \App\Application\Action\Finances\Budget\BudgetSaveAction::class)->setName('finances_budgets_save_all');
            $group_budgets->delete('/delete/{id}', \App\Application\Action\Finances\Budget\BudgetDeleteAction::class)->setName('finances_budgets_delete');

            $group_budgets->get('/costs/', \App\Application\Action\Finances\Budget\BudgetCategoryCostsAction::class)->setName('finances_budgets_category_costs');
        });

        $group->group('/recurring', function (RouteCollectorProxy $group_recurring) {
            $group_recurring->get('/', \App\Application\Action\Finances\Recurring\RecurringListAction::class)->setName('finances_recurring');
            $group_recurring->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Recurring\RecurringEditAction::class)->setName('finances_recurring_edit');
            $group_recurring->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Recurring\RecurringSaveAction::class)->setName('finances_recurring_save');
            $group_recurring->delete('/delete/{id}', \App\Application\Action\Finances\Recurring\RecurringDeleteAction::class)->setName('finances_recurring_delete');
            $group_recurring->get('/trigger/{id}', \App\Application\Action\Finances\Recurring\RecurringTriggerAction::class)->setName('finances_recurring_trigger');
        });

        $group->group('/methods', function (RouteCollectorProxy $group_methods) {
            $group_methods->get('/', \App\Application\Action\Finances\Paymethod\PaymethodListAction::class)->setName('finances_paymethod');
            $group_methods->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Finances\Paymethod\PaymethodEditAction::class)->setName('finances_paymethod_edit');
            $group_methods->post('/save/[{id:[0-9]+}]', \App\Application\Action\Finances\Paymethod\PaymethodSaveAction::class)->setName('finances_paymethod_save');
            $group_methods->delete('/delete/{id}', \App\Application\Action\Finances\Paymethod\PaymethodDeleteAction::class)->setName('finances_paymethod_delete');
        });
    });

    $app->group('/location', function (RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Location\LocationMapAction::class)->setName('location');
        $group->get('/markers', \App\Application\Action\Location\LocationMarkersAction::class)->setName('getMarkers');
        $group->delete('/delete/[{id}]', \App\Application\Action\Location\LocationDeleteAction::class)->setName('delete_marker');
        $group->get('/address/[{id}]', \App\Application\Action\Location\LocationAddressAction::class)->setName('get_address');

        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Location\LocationEditAction::class)->setName('location_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Location\LocationSaveAction::class)->setName('location_save');

        $group->group('/steps', function (RouteCollectorProxy $group_steps) {
            $group_steps->get('/', \App\Application\Action\Location\Steps\StepsAction::class)->setName('steps');
            $group_steps->get('/{year:[0-9]{4}}/', \App\Application\Action\Location\Steps\StepsYearAction::class)->setName('steps_stats_year');
            $group_steps->get('/{year:[0-9]{4}}/{month:[0-9]{1,2}}/', \App\Application\Action\Location\Steps\StepsYearMonthAction::class)->setName('steps_stats_month');

            $group_steps->group('/{date:[0-9]{4}-[0-9]{2}-[0-9]{2}}', function (RouteCollectorProxy $group_steps_date) {
                $group_steps_date->get('/edit/', \App\Application\Action\Location\Steps\StepsEditAction::class)->setName('steps_day_edit');
                $group_steps_date->post('/save/', \App\Application\Action\Location\Steps\StepsSaveAction::class)->setName('steps_save');
            });
        });
    });

    $app->group('/cars', function (RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('car_service'))->withStatus(302);
        });

        $group->group('/service', function (RouteCollectorProxy $group_service) {
            $group_service->get('/', \App\Application\Action\Car\Service\ServiceListAction::class)->setName('car_service');
            $group_service->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Car\Service\ServiceEditAction::class)->setName('car_service_edit');
            $group_service->post('/save/[{id:[0-9]+}]', \App\Application\Action\Car\Service\ServiceSaveAction::class)->setName('car_service_save');
            $group_service->delete('/delete/{id}', \App\Application\Action\Car\Service\ServiceDeleteAction::class)->setName('car_service_delete');

            $group_service->get('/table/fuel/', \App\Application\Action\Car\Service\FuelTableAction::class)->setName('car_service_fuel_table');
            $group_service->get('/table/service/', \App\Application\Action\Car\Service\ServiceTableAction::class)->setName('car_service_service_table');
            $group_service->get('/stats/', \App\Application\Action\Car\Stats\CarServiceStatsAction::class)->setName('car_service_stats');
            $group_service->post('/setYearlyMileageCalcTyp', \App\Application\Action\Car\Stats\CalculationTypeAction::class)->setName('set_mileage_type');
        });

        $group->group('/control', function (RouteCollectorProxy $group_control) {
            $group_control->get('/', \App\Application\Action\Car\Car\CarListAction::class)->setName('cars');
            $group_control->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Car\Car\CarEditAction::class)->setName('cars_edit');
            $group_control->post('/save/[{id:[0-9]+}]', \App\Application\Action\Car\Car\CarSaveAction::class)->setName('cars_save');
            $group_control->delete('/delete/{id}', \App\Application\Action\Car\Car\CarDeleteAction::class)->setName('cars_delete');
        });
    });

    $app->group('/profile', function (RouteCollectorProxy $group) {
        $group->get('/changepassword', \App\Application\Action\Profile\ChangePasswordpageAction::class)->setName('users_change_password');
        $group->post('/changepassword', \App\Application\Action\Profile\ChangePasswordAction::class)->setName('users_change_password');
        $group->get('/image', \App\Application\Action\Profile\ProfileImageAction::class)->setName('users_profile_image');
        $group->post('/image', \App\Application\Action\Profile\ProfileImageSaveAction::class)->setName('users_profile_image');

        $group->get('/edit', \App\Application\Action\Profile\ProfileEditAction::class)->setName('users_profile_edit');
        $group->post('/edit', \App\Application\Action\Profile\ProfileSaveAction::class)->setName('users_profile_edit');

        $group->group('/favorites', function (RouteCollectorProxy $group_favorites) {
            $group_favorites->get('/', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesListAction::class)->setName('users_mobile_favorites');
            $group_favorites->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesEditAction::class)->setName('users_mobile_favorites_edit');
            $group_favorites->post('/save/[{id:[0-9]+}]', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesSaveAction::class)->setName('users_mobile_favorites_save');
            $group_favorites->delete('/delete/{id}', \App\Application\Action\Profile\MobileFavorites\MobileFavoritesDeleteAction::class)->setName('users_mobile_favorites_delete');
        });

        $group->group('/tokens', function (RouteCollectorProxy $group_tokens) {
            $group_tokens->get('/', \App\Application\Action\Profile\LoginTokensListAction::class)->setName('users_login_tokens');
            $group_tokens->delete('/delete/{id}', \App\Application\Action\Profile\LoginTokensDeleteAction::class)->setName('users_login_tokens_delete');
        });

        $group->get('/activity', \App\Application\Action\Activity\ActivityAction::class)->setName('users_activities');
        $group->post('/getActivities', \App\Application\Action\Activity\ActivityListAction::class)->setName('activities_get');

        $group->group('/applicationpasswords', function (RouteCollectorProxy $group_applicationpasswords) {
            $group_applicationpasswords->get('/', \App\Application\Action\Profile\ApplicationPasswords\ApplicationPasswordsListAction::class)->setName('users_application_passwords');
            $group_applicationpasswords->get('/edit/', \App\Application\Action\Profile\ApplicationPasswords\ApplicationPasswordsEditAction::class)->setName('users_application_passwords_edit');
            $group_applicationpasswords->post('/save/[{id:[0-9]+}]', \App\Application\Action\Profile\ApplicationPasswords\ApplicationPasswordsSaveAction::class)->setName('users_application_passwords_save');
            $group_applicationpasswords->delete('/delete/{id}', \App\Application\Action\Profile\ApplicationPasswords\ApplicationPasswordsDeleteAction::class)->setName('users_application_passwords_delete');
        });

        $group->group('/twofactorauth', function (RouteCollectorProxy $group_twofactorauth) {
            $group_twofactorauth->get('/', \App\Application\Action\Profile\TwoFactorAuthPageAction::class)->setName('users_twofactorauth');
            $group_twofactorauth->post('/enable', \App\Application\Action\Profile\TwoFactorAuthEnableAction::class)->setName('users_twofactorauth_enable');
            $group_twofactorauth->post('/disable', \App\Application\Action\Profile\TwoFactorAuthDisableAction::class)->setName('users_twofactorauth_disable');
        });

        $group->group('/frontpage', function (RouteCollectorProxy $group_frontpage) {
            $group_frontpage->get('/', \App\Application\Action\Profile\FrontpageWidgets\FrontpageEditAction::class)->setName('users_profile_frontpage');
            $group_frontpage->get('/options/[{id:[0-9]+}]', \App\Application\Action\Profile\FrontpageWidgets\FrontpageWidgetOptionsAction::class)->setName('users_profile_frontpage_widget_option');
            $group_frontpage->post('/save/[{id:[0-9]+}]', \App\Application\Action\Profile\FrontpageWidgets\FrontpageWidgetSaveAction::class)->setName('users_profile_frontpage_widget_option_save');
            $group_frontpage->post('/updatePosition', \App\Application\Action\Profile\FrontpageWidgets\FrontpageWidgetUpdatePositionAction::class)->setName('users_profile_frontpage_widget_position');
            $group_frontpage->delete('/delete/[{id:[0-9]+}]', \App\Application\Action\Profile\FrontpageWidgets\FrontpageWidgetDeleteAction::class)->setName('users_profile_frontpage_widget_delete');

            $group_frontpage->get('/request/[{id:[0-9]+}]', \App\Application\Action\Profile\FrontpageWidgets\FrontpageWidgetRequestAction::class)->setName('frontpage_widget_request');
        });

        $group->get('/mail/manage/', \App\Application\Action\MailNotifications\MailNotificationsManageAction::class)->setName('mail_manage');
        $group->post('/mail/setCategoryUser', \App\Application\Action\MailNotifications\MailNotificationsSetCategoryAction::class)->setName('mail_notifications_set_category_user');
    });

    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\User\UserListAction::class)->setName('users');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\User\UserEditAction::class)->setName('users_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\User\UserSaveAction::class)->setName('users_save');
        $group->delete('/delete/{id}', \App\Application\Action\User\UserDeleteAction::class)->setName('users_delete');

        $group->group('/tokens', function (RouteCollectorProxy $group_tokens) {
            $group_tokens->get('/', \App\Application\Action\User\LoginTokens\LoginTokensListAction::class)->setName('login_tokens');
            $group_tokens->delete('/delete/{id}', \App\Application\Action\User\LoginTokens\LoginTokensDeleteAction::class)->setName('login_tokens_delete');
            $group_tokens->get('/deleteOld', \App\Application\Action\User\LoginTokens\LoginTokensDeleteOldAction::class)->setName('login_tokens_delete_old');
        });

        $group->group('/{user:[0-9]+}', function (RouteCollectorProxy $group_user) {
            $group_user->get('/testmail', \App\Application\Action\User\TestMailAction::class)->setName('users_test_mail');
            $group_user->get('/identity', \App\Application\Action\User\TakeIdentityAction::class)->setName('users_take_identity');
        });
    })->add(\App\Application\Middleware\AdminMiddleware::class);

    $app->get('/usersearch', \App\Application\Action\User\UserSearchAction::class)->setName('usersearch');

    $app->group('/notifications', function (RouteCollectorProxy $group) {

        $group->group('/clients', function (RouteCollectorProxy $group_clients) {
            $group_clients->get('/', \App\Application\Action\Notifications\Clients\NotificationClientsListAction::class)->setName('notifications_clients');
            $group_clients->delete('/delete/{id}', \App\Application\Action\Notifications\Clients\NotificationClientsDeleteAction::class)->setName('notifications_clients_delete');
            $group_clients->get('/test/{id:[0-9]+}', \App\Application\Action\Notifications\Clients\NotificationClientsTestAction::class)->setName('notifications_clients_test');
            $group_clients->post('/test/{id:[0-9]+}', \App\Application\Action\Notifications\Clients\NotificationClientsTestSendAction::class)->setName('notifications_clients_test');
        })->add(\App\Application\Middleware\AdminMiddleware::class);

        $group->group('/categories', function (RouteCollectorProxy $group_categories) {
            $group_categories->get('/', \App\Application\Action\Notifications\Categories\NotificationCategoryListAction::class)->setName('notifications_categories');
            $group_categories->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Notifications\Categories\NotificationCategoryEditAction::class)->setName('notifications_categories_edit');
            $group_categories->post('/save/[{id:[0-9]+}]', \App\Application\Action\Notifications\Categories\NotificationCategorySaveAction::class)->setName('notifications_categories_save');
            $group_categories->delete('/delete/{id}', \App\Application\Action\Notifications\Categories\NotificationCategoryDeleteAction::class)->setName('notifications_categories_delete');
        })->add(\App\Application\Middleware\AdminMiddleware::class);

        $group->get('/', \App\Application\Action\Notifications\NotificationsAction::class)->setName('notifications');
        $group->get('/manage/', \App\Application\Action\Notifications\NotificationsManageAction::class)->setName('notifications_clients_manage');

        $group->post('/subscribe/', \App\Application\Action\Notifications\Clients\NotificationClientsCreateAPIAction::class)->setName('notifications_clients_subscribe');
        $group->put('/subscribe/', \App\Application\Action\Notifications\Clients\NotificationClientsUpdateAPIAction::class)->setName('notifications_clients_subscribe');
        $group->delete('/subscribe/', \App\Application\Action\Notifications\Clients\NotificationClientsDeleteAPIAction::class)->setName('notifications_clients_subscribe');

        // use post because endpoint param is too complex for a GET param
        $group->post('/getCategories', \App\Application\Action\Notifications\Clients\NotificationClientsCategoriesAction::class)->setName('notifications_clients_categories');
        $group->post('/setCategorySubscription', \App\Application\Action\Notifications\Clients\NotificationClientsSetCategoryAction::class)->setName('notifications_clients_set_category');
        $group->post('/setCategoryUser', \App\Application\Action\Notifications\Users\NotificationClientsSetCategoryAction::class)->setName('notifications_clients_set_category_user');

        $group->post('/getNotifications', \App\Application\Action\Notifications\NotificationsListAction::class)->setName('notifications_get');
    });

    $app->group('/boards', function (RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Board\Board\BoardListAction::class)->setName('boards');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Board\Board\BoardEditAction::class)->setName('boards_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Board\Board\BoardSaveAction::class)->setName('boards_save');
        $group->delete('/delete/{id}', \App\Application\Action\Board\Board\BoardDeleteAction::class)->setName('boards_delete');

        $group->group('/view', function (RouteCollectorProxy $group_view) {
            $group_view->get('/{hash}', \App\Application\Action\Board\Board\BoardViewAction::class)->setName('boards_view');
        });

        $group->group('/stacks', function (RouteCollectorProxy $group_stacks) {
            $group_stacks->post('/save/[{id:[0-9]+}]', \App\Application\Action\Board\Stack\StackSaveAction::class)->setName('stack_save');
            $group_stacks->post('/updatePosition', \App\Application\Action\Board\Stack\StackUpdatePositionAction::class)->setName('stack_update_position');
            $group_stacks->delete('/delete/[{id:[0-9]+}]', \App\Application\Action\Board\Stack\StackDeleteAction::class)->setName('stack_delete');
            $group_stacks->post('/archive/[{id:[0-9]+}]', \App\Application\Action\Board\Stack\StackArchiveAction::class)->setName('stack_archive');
            $group_stacks->get('/data/[{id:[0-9]+}]', \App\Application\Action\Board\Stack\StackDataAction::class)->setName('stack_get');
        });
        $group->group('/card', function (RouteCollectorProxy $group_cards) {
            $group_cards->post('/save/[{id:[0-9]+}]', \App\Application\Action\Board\Card\CardSaveAction::class)->setName('card_save');
            $group_cards->post('/updatePosition', \App\Application\Action\Board\Card\CardUpdatePositionAction::class)->setName('card_update_position');
            $group_cards->post('/moveCard', \App\Application\Action\Board\Card\CardMoveStackAction::class)->setName('card_move_stack');
            $group_cards->get('/data/[{id:[0-9]+}]', \App\Application\Action\Board\Card\CardDataAction::class)->setName('card_get');
            $group_cards->delete('/delete/[{id:[0-9]+}]', \App\Application\Action\Board\Card\CardDeleteAction::class)->setName('card_delete');
            $group_cards->post('/archive/[{id:[0-9]+}]', \App\Application\Action\Board\Card\CardArchiveAction::class)->setName('card_archive');
        });

        $group->group('/labels', function (RouteCollectorProxy $group_labels) {
            $group_labels->post('/save/[{id:[0-9]+}]', \App\Application\Action\Board\Label\LabelSaveAction::class)->setName('label_save');
            $group_labels->delete('/delete/[{id:[0-9]+}]', \App\Application\Action\Board\Label\LabelDeleteAction::class)->setName('label_delete');
            $group_labels->get('/data/[{id:[0-9]+}]', \App\Application\Action\Board\Label\LabelDataAction::class)->setName('label_get');
        });

        $group->post('/setArchive', \App\Application\Action\Board\Board\BoardChangeViewArchivedAction::class)->setName('set_archive');
    });

    $app->group('/crawlers', function (RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Crawler\Crawler\CrawlerListAction::class)->setName('crawlers');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Crawler\Crawler\CrawlerEditAction::class)->setName('crawlers_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Crawler\Crawler\CrawlerSaveAction::class)->setName('crawlers_save');
        $group->delete('/delete/{id}', \App\Application\Action\Crawler\Crawler\CrawlerDeleteAction::class)->setName('crawlers_delete');
        $group->get('/deleteOld/{id}', \App\Application\Action\Crawler\Dataset\DatasetDeleteOldAction::class)->setName('crawlers_dataset_delete_old');

        $group->group('/{crawler}', function (RouteCollectorProxy $group_crawler) {

            $group_crawler->get('/view/', \App\Application\Action\Crawler\Crawler\CrawlerViewAction::class)->setName('crawlers_view');
            $group_crawler->get('/table/', \App\Application\Action\Crawler\Crawler\CrawlerTableAction::class)->setName('crawlers_table');
            $group_crawler->post('/setFilter/', \App\Application\Action\Crawler\Crawler\CrawlerSetFilterAction::class)->setName('set_crawler_filter');

            $group_crawler->post('/save/', \App\Application\Action\Crawler\Dataset\CrawlerSaveDatasetAction::class)->setName('crawler_dataset_save');
            $group_crawler->get('/saved/', \App\Application\Action\Crawler\Dataset\DatasetSavedListAction::class)->setName('crawler_dataset_saved_list');

            $group_crawler->group('/headers', function (RouteCollectorProxy $group_header) {
                $group_header->get('/', \App\Application\Action\Crawler\Header\HeaderListAction::class)->setName('crawlers_headers');
                $group_header->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Crawler\Header\HeaderEditAction::class)->setName('crawlers_headers_edit');
                $group_header->post('/save/[{id:[0-9]+}]', \App\Application\Action\Crawler\Header\HeaderSaveAction::class)->setName('crawlers_headers_save');
                $group_header->delete('/delete/{id}', \App\Application\Action\Crawler\Header\HeaderDeleteAction::class)->setName('crawlers_headers_delete');

                $group_header->get('/clone/', \App\Application\Action\Crawler\Header\HeaderCloneAction::class)->setName('crawlers_headers_clone');
                $group_header->post('/cloning/', \App\Application\Action\Crawler\Header\HeaderCloningAction::class)->setName('crawlers_headers_cloning');
            });

            $group_crawler->group('/links', function (RouteCollectorProxy $group_links) {
                $group_links->get('/', \App\Application\Action\Crawler\Link\LinkListAction::class)->setName('crawlers_links');
                $group_links->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Crawler\Link\LinkEditAction::class)->setName('crawlers_links_edit');
                $group_links->post('/save/[{id:[0-9]+}]', \App\Application\Action\Crawler\Link\LinkSaveAction::class)->setName('crawlers_links_save');
                $group_links->delete('/delete/{id}', \App\Application\Action\Crawler\Link\LinkDeleteAction::class)->setName('crawlers_links_delete');
            });
        });
    });

    $app->group('/splitbills', function (RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('splitbills'))->withStatus(302);
        });

        $group->group('/groups', function (RouteCollectorProxy $group_groups) {
            $group_groups->get('/', \App\Application\Action\Splitbill\Group\GroupListAction::class)->setName('splitbills');
            $group_groups->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Splitbill\Group\GroupEditAction::class)->setName('splitbill_groups_edit');
            $group_groups->post('/save/[{id:[0-9]+}]', \App\Application\Action\Splitbill\Group\GroupSaveAction::class)->setName('splitbill_groups_save');
            $group_groups->delete('/delete/{id}', \App\Application\Action\Splitbill\Group\GroupDeleteAction::class)->setName('splitbill_groups_delete');
        });

        $group->group('/{group}', function (RouteCollectorProxy $group_group) {

            $group_group->get('/view/', \App\Application\Action\Splitbill\Bill\BillViewAction::class)->setName('splitbill_bills');
            $group_group->get('/table/', \App\Application\Action\Splitbill\Bill\BillTableAction::class)->setName('splitbill_bills_table');

            $group_group->group('/bills', function (RouteCollectorProxy $group_bill) {
                $group_bill->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Splitbill\Bill\BillEditAction::class)->setName('splitbill_bills_edit');
                $group_bill->post('/save/[{id:[0-9]+}]', \App\Application\Action\Splitbill\Bill\BillSaveAction::class)->setName('splitbill_bills_save');
                $group_bill->delete('/delete/{id}', \App\Application\Action\Splitbill\Bill\BillDeleteAction::class)->setName('splitbill_bills_delete');
            });

            $group_group->group('/recurring', function (RouteCollectorProxy $group_recurring) {
                $group_recurring->get('/', \App\Application\Action\Splitbill\RecurringBill\RecurringBillListAction::class)->setName('splitbill_bills_recurring');
                $group_recurring->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Splitbill\RecurringBill\RecurringBillEditAction::class)->setName('splitbill_bill_recurring_edit');
                $group_recurring->post('/save/[{id:[0-9]+}]', \App\Application\Action\Splitbill\RecurringBill\RecurringBillSaveAction::class)->setName('splitbill_bills_recurring_save');
                $group_recurring->delete('/delete/{id}', \App\Application\Action\Splitbill\RecurringBill\RecurringBillDeleteAction::class)->setName('splitbill_bills_recurring_delete');
                $group_recurring->get('/trigger/{id}', \App\Application\Action\Splitbill\RecurringBill\RecurringBillTriggerAction::class)->setName('splitbill_bill_recurring_trigger');
            });
        });
    });

    $app->group('/trips', function (RouteCollectorProxy $group) {
        $group->get('/', \App\Application\Action\Trips\Trip\TripListAction::class)->setName('trips');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Trips\Trip\TripEditAction::class)->setName('trips_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Trips\Trip\TripSaveAction::class)->setName('trips_save');
        $group->delete('/delete/{id}', \App\Application\Action\Trips\Trip\TripDeleteAction::class)->setName('trips_delete');

        $group->get('/search/', \App\Application\Action\Trips\Event\GeoSearchAction::class)->setName('get_location_of_address');

        $group->group('/{trip}', function (RouteCollectorProxy $group_trip) {

            $group_trip->get('/view/', \App\Application\Action\Trips\Event\EventViewAction::class)->setName('trips_view');
            $group_trip->get('/markers/', \App\Application\Action\Trips\Event\EventMarkersAction::class)->setName('trips_markers');

            $group_trip->group('/tripevent', function (RouteCollectorProxy $group_event) {
                $group_event->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Trips\Event\EventEditAction::class)->setName('trips_event_edit');
                $group_event->post('/save/[{id:[0-9]+}]', \App\Application\Action\Trips\Event\EventSaveAction::class)->setName('trips_event_save');
                $group_event->delete('/delete/{id}', \App\Application\Action\Trips\Event\EventDeleteAction::class)->setName('trips_event_delete');

                $group_event->post('/image/{id:[0-9]+}', \App\Application\Action\Trips\Event\EventImageSaveAction::class)->setName('trips_event_image');
                $group_event->delete('/imagedelete/{id:[0-9]+}', \App\Application\Action\Trips\Event\EventImageDeleteAction::class)->setName('trips_event_image_delete');

                $group_event->post('/updatePosition', \App\Application\Action\Trips\Event\EventUpdatePositionAction::class)->setName('trips_event_position');
            });

            $group_trip->group('/waypoint', function (RouteCollectorProxy $group_waypoint) {
                $group_waypoint->post('/add', \App\Application\Action\Trips\Waypoint\WaypointSaveAction::class)->setName('trips_add_waypoint');
                $group_waypoint->delete('/delete', \App\Application\Action\Trips\Waypoint\WaypointDeleteAction::class)->setName('trips_delete_waypoint');
            });

            $group_trip->group('/route', function (RouteCollectorProxy $group_route) {
                $group_route->post('/add', \App\Application\Action\Trips\Route\RouteSaveAction::class)->setName('trips_add_route');
                $group_route->get('/list', \App\Application\Action\Trips\Route\RouteListAction::class)->setName('trips_list_route');
                $group_route->get('/getWaypoints', \App\Application\Action\Trips\Route\RouteWaypointsAction::class)->setName('trips_route_waypoints');
                $group_route->delete('/delete/{id}', \App\Application\Action\Trips\Route\RouteDeleteAction::class)->setName('trips_delete_route');
            });
        });
    });

    $app->group('/timesheets', function (RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('timesheets'))->withStatus(302);
        });

        $group->group('/projects', function (RouteCollectorProxy $group_projects) {
            $group_projects->get('/', \App\Application\Action\Timesheets\Project\ProjectListAction::class)->setName('timesheets');
            $group_projects->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Timesheets\Project\ProjectEditAction::class)->setName('timesheets_projects_edit');
            $group_projects->post('/save/[{id:[0-9]+}]', \App\Application\Action\Timesheets\Project\ProjectSaveAction::class)->setName('timesheets_projects_save');
            $group_projects->delete('/delete/{id}', \App\Application\Action\Timesheets\Project\ProjectDeleteAction::class)->setName('timesheets_projects_delete');
        });

        $group->group('/{project}', function (RouteCollectorProxy $group_project) {

            $group_project->post('/check', \App\Application\Action\Timesheets\Project\ProjectCheckPasswordAction::class)->setName('timesheets_sheets_check_pw');
            
            $group_project->get('/view/', \App\Application\Action\Timesheets\Sheet\SheetViewAction::class)->setName('timesheets_sheets');
            $group_project->get('/table/', \App\Application\Action\Timesheets\Sheet\SheetTableAction::class)->setName('timesheets_sheets_table');

            $group_project->group('/sheets', function (RouteCollectorProxy $group_sheets) {
                $group_sheets->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Timesheets\Sheet\SheetEditAction::class)->setName('timesheets_sheets_edit');
                $group_sheets->post('/save/[{id:[0-9]+}]', \App\Application\Action\Timesheets\Sheet\SheetSaveAction::class)->setName('timesheets_sheets_save');
                $group_sheets->delete('/delete/{id}', \App\Application\Action\Timesheets\Sheet\SheetDeleteAction::class)->setName('timesheets_sheets_delete');
                
                $group_sheets->post('/setCategories', \App\Application\Action\Timesheets\Sheet\SheetSetCategoriesAction::class)->setName('timesheets_sheets_set_categories');
                
                $group_sheets->get('/notice/', \App\Application\Action\Timesheets\SheetNotice\SheetNoticeDataAction::class)->setName('timesheets_sheets_notice_data');
                $group_sheets->group('/notice/{sheet:[0-9]+}', function (RouteCollectorProxy $group_notice) {
                    $group_notice->get('/edit/', \App\Application\Action\Timesheets\SheetNotice\SheetNoticeEditAction::class)->setName('timesheets_sheets_notice_edit');
                    $group_notice->post('/save/', \App\Application\Action\Timesheets\SheetNotice\SheetNoticeSaveAction::class)->setName('timesheets_sheets_notice_save');
                });
            });

            $group_project->group('/fast', function (RouteCollectorProxy $group_fast) {
                $group_fast->get('/', \App\Application\Action\Timesheets\Sheet\SheetFastAction::class)->setName('timesheets_fast');
                $group_fast->post('/checkin', \App\Application\Action\Timesheets\Sheet\SheetFastCheckInAction::class)->setName('timesheets_fast_checkin');
                $group_fast->post('/checkout', \App\Application\Action\Timesheets\Sheet\SheetFastCheckOutAction::class)->setName('timesheets_fast_checkout');
            });

            $group_project->group('/export', function (RouteCollectorProxy $group_export) {
                $group_export->get('/', \App\Application\Action\Timesheets\Sheet\SheetExportViewAction::class)->setName('timesheets_export');
                $group_export->get('/download', \App\Application\Action\Timesheets\Sheet\SheetExportAction::class)->setName('timesheets_sheets_export');
            });

            $group_project->group('/categories', function (RouteCollectorProxy $group_category) {
                $group_category->get('/', \App\Application\Action\Timesheets\ProjectCategory\ProjectCategoryListAction::class)->setName('timesheets_project_categories');
                $group_category->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Timesheets\ProjectCategory\ProjectCategoryEditAction::class)->setName('timesheets_project_categories_edit');
                $group_category->post('/save/[{id:[0-9]+}]', \App\Application\Action\Timesheets\ProjectCategory\ProjectCategorySaveAction::class)->setName('timesheets_project_categories_save');
                $group_category->delete('/delete/{id}', \App\Application\Action\Timesheets\ProjectCategory\ProjectCategoryDeleteAction::class)->setName('timesheets_project_categories_delete');
            });
            
            $group_project->group('/categorybudget', function (RouteCollectorProxy $group_category_budget) {
                $group_category_budget->get('/', \App\Application\Action\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetListAction::class)->setName('timesheets_project_categorybudget');
                $group_category_budget->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetEditAction::class)->setName('timesheets_project_categorybudget_edit');
                $group_category_budget->post('/save/[{id:[0-9]+}]', \App\Application\Action\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetSaveAction::class)->setName('timesheets_project_categorybudget_save');
                $group_category_budget->delete('/delete/{id}', \App\Application\Action\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetDeleteAction::class)->setName('timesheets_project_categorybudget_delete');
                
                $group_category_budget->get('/view/', \App\Application\Action\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetViewAction::class)->setName('timesheets_project_categorybudget_view');
            });
        });
    });

    $app->group('/workouts', function (RouteCollectorProxy $group) {

        $group->get('/', function (Request $request, Response $response) {
            return $response->withHeader('Location', $this->get(RouteParser::class)->urlFor('workouts'))->withStatus(302);
        });

        $group->group('/{plan}', function (RouteCollectorProxy $group_plan) {
            $group_plan->get('/view/', \App\Application\Action\Workouts\Plan\PlanViewAction::class)->setName('workouts_plan_view');

            $group_plan->get('/export', \App\Application\Action\Workouts\Plan\PlanExportAction::class)->setName('workouts_plan_export');

            $group_plan->group('/sessions', function (RouteCollectorProxy $group_session) {
                $group_session->get('/', \App\Application\Action\Workouts\Session\SessionListAction::class)->setName('workouts_sessions');
                $group_session->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Workouts\Session\SessionEditAction::class)->setName('workouts_sessions_edit');
                $group_session->post('/save/[{id:[0-9]+}]', \App\Application\Action\Workouts\Session\SessionSaveAction::class)->setName('workouts_sessions_save');
                $group_session->delete('/delete/{id}', \App\Application\Action\Workouts\Session\SessionDeleteAction::class)->setName('workouts_sessions_delete');

                $group_session->get('/view/{id:[0-9]+}', \App\Application\Action\Workouts\Session\SessionViewAction::class)->setName('workouts_sessions_view');

                $group_session->get('/stats', \App\Application\Action\Workouts\Session\SessionStatsAction::class)->setName('workouts_sessions_stats');
            });
        });

        $group->get('/stats', \App\Application\Action\Workouts\Session\SessionStatsAllAction::class)->setName('workouts_sessions_stats_all');

        $group->group('/plans', function (RouteCollectorProxy $group_plans) {
            $group_plans->get('/', \App\Application\Action\Workouts\Plan\PlanListAction::class)->setName('workouts');
            $group_plans->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Workouts\Plan\PlanEditAction::class)->setName('workouts_plans_edit');
            $group_plans->post('/save/[{id:[0-9]+}]', \App\Application\Action\Workouts\Plan\PlanSaveAction::class)->setName('workouts_plans_save');
            $group_plans->delete('/delete/{id}', \App\Application\Action\Workouts\Plan\PlanDeleteAction::class)->setName('workouts_plans_delete');
        });

        $group->group('/templates', function (RouteCollectorProxy $group_templates) {
            $group_templates->get('/', \App\Application\Action\Workouts\Template\TemplateListAction::class)->setName('workouts_templates');
            $group_templates->get('/{plan}/view/', \App\Application\Action\Workouts\Template\TemplateViewAction::class)->setName('workouts_template_view');

            $group_templates->group('/manage', function (RouteCollectorProxy $group_templates_admin) {
                $group_templates_admin->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Workouts\Template\TemplateEditAction::class)->setName('workouts_templates_edit');
                $group_templates_admin->post('/save/[{id:[0-9]+}]', \App\Application\Action\Workouts\Template\TemplateSaveAction::class)->setName('workouts_templates_save');
                $group_templates_admin->delete('/delete/{id}', \App\Application\Action\Workouts\Template\TemplateDeleteAction::class)->setName('workouts_templates_delete');
            })->add(\App\Application\Middleware\AdminMiddleware::class);
        });

        $group->group('/exercises', function (RouteCollectorProxy $group_exercises) {

            $group_exercises->get('/view', \App\Application\Action\Workouts\Exercise\ExerciseViewAction::class)->setName('workouts_exercises_view');
            $group_exercises->get('/list', \App\Application\Action\Workouts\Exercise\ExercisesListAction::class)->setName('workouts_exercises_get');
            $group_exercises->post('/muscles', \App\Application\Action\Workouts\Exercise\ExercisesSelectedMusclesAction::class)->setName('workouts_exercises_selected_muscles');
            $group_exercises->get('/data', \App\Application\Action\Workouts\Exercise\ExercisesDataAction::class)->setName('workouts_exercises_data');

            $group_exercises->group('/manage', function (RouteCollectorProxy $group_exercises_admin) {
                $group_exercises_admin->get('/', \App\Application\Action\Workouts\Exercise\ExerciseListAction::class)->setName('workouts_exercises');
                $group_exercises_admin->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Workouts\Exercise\ExerciseEditAction::class)->setName('workouts_exercises_edit');
                $group_exercises_admin->post('/save/[{id:[0-9]+}]', \App\Application\Action\Workouts\Exercise\ExerciseSaveAction::class)->setName('workouts_exercises_save');
                $group_exercises_admin->delete('/delete/{id}', \App\Application\Action\Workouts\Exercise\ExerciseDeleteAction::class)->setName('workouts_exercises_delete');
            })->add(\App\Application\Middleware\AdminMiddleware::class);
        });

        $group->group('/muscles', function (RouteCollectorProxy $group_muscles) {
            $group_muscles->get('/', \App\Application\Action\Workouts\Muscle\MuscleListAction::class)->setName('workouts_muscles');
            $group_muscles->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Workouts\Muscle\MuscleEditAction::class)->setName('workouts_muscles_edit');
            $group_muscles->post('/save/[{id:[0-9]+}]', \App\Application\Action\Workouts\Muscle\MuscleSaveAction::class)->setName('workouts_muscles_save');
            $group_muscles->delete('/delete/{id}', \App\Application\Action\Workouts\Muscle\MuscleDeleteAction::class)->setName('workouts_muscles_delete');

            $group_muscles->get('/image', \App\Application\Action\Workouts\Muscle\MuscleBaseImageAction::class)->setName('workouts_muscles_image');
            $group_muscles->post('/image', \App\Application\Action\Workouts\Muscle\MuscleBaseImageSaveAction::class)->setName('workouts_muscles_image');
        })->add(\App\Application\Middleware\AdminMiddleware::class);

        $group->group('/bodyparts', function (RouteCollectorProxy $group_bodyparts) {
            $group_bodyparts->get('/', \App\Application\Action\Workouts\Bodypart\BodypartListAction::class)->setName('workouts_bodyparts');
            $group_bodyparts->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Workouts\Bodypart\BodypartEditAction::class)->setName('workouts_bodyparts_edit');
            $group_bodyparts->post('/save/[{id:[0-9]+}]', \App\Application\Action\Workouts\Bodypart\BodypartSaveAction::class)->setName('workouts_bodyparts_save');
            $group_bodyparts->delete('/delete/{id}', \App\Application\Action\Workouts\Bodypart\BodypartDeleteAction::class)->setName('workouts_bodyparts_delete');
        })->add(\App\Application\Middleware\AdminMiddleware::class);
    });

    $app->group('/recipes', function (RouteCollectorProxy $group) {


        $group->get('/', \App\Application\Action\Recipes\Recipe\RecipeListAction::class)->setName('recipes');
        $group->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Recipes\Recipe\RecipeEditAction::class)->setName('recipes_edit');
        $group->post('/save/[{id:[0-9]+}]', \App\Application\Action\Recipes\Recipe\RecipeSaveAction::class)->setName('recipes_save');
        $group->delete('/delete/{id}', \App\Application\Action\Recipes\Recipe\RecipeDeleteAction::class)->setName('recipes_delete');

        $group->get('/list', \App\Application\Action\Recipes\Recipe\RecipesListAction::class)->setName('recipes_get');
        $group->get('/listForMealplan', \App\Application\Action\Recipes\Recipe\RecipesListForMealplanAction::class)->setName('recipes_get_mealplan');

        $group->group('/{recipe}', function (RouteCollectorProxy $group_recipe) {
            $group_recipe->get('/view', \App\Application\Action\Recipes\Recipe\RecipeViewAction::class)->setName('recipes_recipe_view');
            $group_recipe->get('/addtocookbook', \App\Application\Action\Recipes\Recipe\RecipeAddToCookbookAction::class)->setName('recipes_recipe_add_to_cookbook');
        });

        $group->group('/cookbooks', function (RouteCollectorProxy $group_cookbooks) {
            $group_cookbooks->get('/', \App\Application\Action\Recipes\Cookbook\CookbookListAction::class)->setName('recipes_cookbooks');
            $group_cookbooks->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Recipes\Cookbook\CookbookEditAction::class)->setName('recipes_cookbooks_edit');
            $group_cookbooks->post('/save/[{id:[0-9]+}]', \App\Application\Action\Recipes\Cookbook\CookbookSaveAction::class)->setName('recipes_cookbooks_save');
            $group_cookbooks->delete('/delete/{id}', \App\Application\Action\Recipes\Cookbook\CookbookDeleteAction::class)->setName('recipes_cookbooks_delete');

            $group_cookbooks->post('/addrecipe/', \App\Application\Action\Recipes\Cookbook\CookbookAddRecipeAction::class)->setName('recipes_cookbooks_add_recipe');

            $group_cookbooks->group('/{cookbook}', function (RouteCollectorProxy $group_cookbook) {
                $group_cookbook->get('/view/', \App\Application\Action\Recipes\Cookbook\CookbookViewAction::class)->setName('recipes_cookbooks_view');
                $group_cookbook->get('/view/{recipe}', \App\Application\Action\Recipes\Cookbook\CookbookViewRecipeAction::class)->setName('recipes_cookbooks_view_recipe');
                $group_cookbook->delete('/removerecipe/', \App\Application\Action\Recipes\Cookbook\CookbookRemoveRecipeAction::class)->setName('recipes_cookbooks_remove_recipe');
            });
        });

        $group->group('/ingredients', function (RouteCollectorProxy $group_ingredients) {
            $group_ingredients->get('/', \App\Application\Action\Recipes\Ingredient\IngredientListAction::class)->setName('recipes_ingredients');
            $group_ingredients->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Recipes\Ingredient\IngredientEditAction::class)->setName('recipes_ingredients_edit');
            $group_ingredients->post('/save/[{id:[0-9]+}]', \App\Application\Action\Recipes\Ingredient\IngredientSaveAction::class)->setName('recipes_ingredients_save');
            $group_ingredients->delete('/delete/{id}', \App\Application\Action\Recipes\Ingredient\IngredientDeleteAction::class)->setName('recipes_ingredients_delete');

            $group_ingredients->get('/list', \App\Application\Action\Recipes\Ingredient\IngredientSelectionListAction::class)->setName('ingredients_get');
        });
        
        $group->group('/mealplans', function (RouteCollectorProxy $group_mealplans) {
            $group_mealplans->get('/', \App\Application\Action\Recipes\Mealplan\MealplanListAction::class)->setName('recipes_mealplans');
            $group_mealplans->get('/edit/[{id:[0-9]+}]', \App\Application\Action\Recipes\Mealplan\MealplanEditAction::class)->setName('recipes_mealplans_edit');
            $group_mealplans->post('/save/[{id:[0-9]+}]', \App\Application\Action\Recipes\Mealplan\MealplanSaveAction::class)->setName('recipes_mealplans_save');
            $group_mealplans->delete('/delete/{id}', \App\Application\Action\Recipes\Mealplan\MealplanDeleteAction::class)->setName('recipes_mealplans_delete');

            $group_mealplans->group('/{mealplan}', function (RouteCollectorProxy $group_mealplan) {
                $group_mealplan->get('/view/', \App\Application\Action\Recipes\Mealplan\MealplanViewAction::class)->setName('recipes_mealplans_view');
                
                $group_mealplan->post('/moverecipe/', \App\Application\Action\Recipes\Mealplan\MealplanMoveRecipeAction::class)->setName('recipes_mealplans_move_recipe');
                $group_mealplan->delete('/removerecipe/', \App\Application\Action\Recipes\Mealplan\MealplanRemoveRecipeAction::class)->setName('recipes_mealplans_remove_recipe');
            });
        });
        
    });

    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->group('/location', function (RouteCollectorProxy $location_group) {
            $location_group->post('/record', \App\Application\Action\Location\LocationRecordAction::class)->setName('location_record');
        });
        $group->group('/crawlers', function (RouteCollectorProxy $crawler_group) {
            $crawler_group->post('/record', \App\Application\Action\Crawler\Dataset\DatasetRecordAction::class)->setName('crawler_record');
        });
        $group->group('/notifications', function (RouteCollectorProxy $notifications_group) {
            $notifications_group->get('/notify', \App\Application\Action\Notifications\NotificationsNotifyByCategoryAction::class);
        });

        $group->post('/workout', \App\Application\Action\Workouts\Exercise\ExerciseSaveAction::class);
    });
};
