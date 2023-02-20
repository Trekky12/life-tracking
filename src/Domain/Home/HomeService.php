<?php

namespace App\Domain\Home;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Home\Widget\LastFinanceEntriesWidget;
use App\Domain\Home\Widget\StepsTodayWidget;
use App\Domain\Home\Widget\FinanceMonthExpensesWidget;
use App\Domain\Home\Widget\FinanceMonthIncomeWidget;
use App\Domain\Home\Widget\FinanceMonthBalanceWidget;
use App\Domain\Home\Widget\CarMaxMileageTodayWidget;
use App\Domain\Home\Widget\CarLastRefuelWidget;
use App\Domain\Home\Widget\SplittedBillsBalanceWidget;
use App\Domain\Home\Widget\TimesheetsSumWidget;
use App\Domain\Home\Widget\TimesheetsProjectBudgetWidget;
use App\Domain\Home\Widget\TimesheetsFastCreateWidget;
use App\Domain\Home\Widget\BoardsCardsWidget;
use App\Domain\Home\Widget\ShoppingListWidget;
use App\Domain\Home\Widget\WidgetMapper;
use App\Domain\Base\Settings;
use App\Domain\Main\Translator;
use App\Domain\Main\Helper;
use App\Domain\Home\Widget\EFAWidget;
use App\Domain\Home\Widget\CurrentWeatherWidget;
use App\Domain\Home\Widget\WeatherForecastWidget;
use Slim\Routing\RouteParser;

class HomeService extends Service
{

    private $translation;
    private $settings;
    private $helper;
    private $last_finance_entries_widget;
    private $steps_today_widget;
    private $finance_month_expenses_widget;
    private $finance_month_income_widget;
    private $finance_month_balance_widget;
    private $car_max_mileage_today_widget;
    private $car_last_refuel_widget;
    private $splitted_bills_balance_widget;
    private $timesheets_sum_widget;
    private $timesheets_project_budget_widget;
    private $timesheets_fast_create_widget;
    private $efa_widget;
    private $currentweather_widget;
    private $weatherforecast_widget;
    private $boards_cards_widget;
    private $shoppinglist_widget;
    private $widget_mapper;
    private $router;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        Translator $translation,
        Settings $settings,
        Helper $helper,
        LastFinanceEntriesWidget $last_finance_entries_widget,
        StepsTodayWidget $steps_today_widget,
        FinanceMonthExpensesWidget $finance_month_expenses_widget,
        FinanceMonthIncomeWidget $finance_month_income_widget,
        FinanceMonthBalanceWidget $finance_month_balance_widget,
        CarMaxMileageTodayWidget $car_max_mileage_today_widget,
        CarLastRefuelWidget $car_last_refuel_widget,
        SplittedBillsBalanceWidget $splitted_bills_balance_widget,
        TimesheetsSumWidget $timesheets_sum_widget,
        TimesheetsProjectBudgetWidget $timesheets_project_budget_widget,
        TimesheetsFastCreateWidget $timesheets_fast_create_widget,
        EFAWidget $efa_widget,
        CurrentWeatherWidget $currentweather_widget,
        WeatherForecastWidget $weatherforecast_widget,
        BoardsCardsWidget $boards_cards_widget,
        ShoppingListWidget $shoppinglist_widget,
        WidgetMapper $widget_mapper,
        RouteParser $router
    ) {
        parent::__construct($logger, $user);
        $this->translation = $translation;
        $this->settings = $settings;
        $this->helper = $helper;

        $this->last_finance_entries_widget = $last_finance_entries_widget;
        $this->steps_today_widget = $steps_today_widget;
        $this->finance_month_expenses_widget = $finance_month_expenses_widget;
        $this->finance_month_income_widget = $finance_month_income_widget;
        $this->finance_month_balance_widget = $finance_month_balance_widget;
        $this->car_max_mileage_today_widget = $car_max_mileage_today_widget;
        $this->car_last_refuel_widget = $car_last_refuel_widget;
        $this->splitted_bills_balance_widget = $splitted_bills_balance_widget;
        $this->timesheets_sum_widget = $timesheets_sum_widget;
        $this->timesheets_project_budget_widget = $timesheets_project_budget_widget;
        $this->timesheets_fast_create_widget = $timesheets_fast_create_widget;
        $this->efa_widget = $efa_widget;
        $this->currentweather_widget = $currentweather_widget;
        $this->weatherforecast_widget = $weatherforecast_widget;
        $this->boards_cards_widget = $boards_cards_widget;
        $this->shoppinglist_widget = $shoppinglist_widget;

        $this->widget_mapper = $widget_mapper;

        $this->router = $router;
    }

    public function getPWAStartPage()
    {
        $user = $this->current_user->getUser();
        if (!is_null($user) && !empty($user->start_url)) {
            return new Payload(Payload::$RESULT_HTML, ["url" => $user->start_url]);
        }
        return new Payload(Payload::$RESULT_HTML, ["url" => $this->router->urlFor('index')]);
    }

    public function getUserStartPage()
    {
        $widgets = $this->widget_mapper->getAll('position');
        $list = [];
        if (count($widgets) > 0) {
            foreach ($widgets as $widget_object) {
                $list[] = $this->getWidgetForFrontend($widget_object);
            }
        } else {
            $modules = $this->settings->getAppSettings()['modules'];
            foreach ($modules as $module_name => $module) {
                if ($this->current_user->getUser()->hasModule($module_name)) {
                    if ($module_name == "location" && $this->steps_today_widget->getContent() > 0) {
                        $w = new Widget\WidgetObject(["name" => "steps_today_entries"]);
                        $list[] = $this->getWidgetForFrontend($w);
                    } elseif ($module_name == "finances") {
                        $w1 = new Widget\WidgetObject(["name" => "last_finance_entries"]);
                        $w2 = new Widget\WidgetObject(["name" => "finances_month_expenses"]);
                        $w3 = new Widget\WidgetObject(["name" => "finances_month_income"]);
                        $w4 = new Widget\WidgetObject(["name" => "finances_month_balance"]);
                        $list[] = $this->getWidgetForFrontend($w1);
                        $list[] = $this->getWidgetForFrontend($w2);
                        $list[] = $this->getWidgetForFrontend($w3);
                        $list[] = $this->getWidgetForFrontend($w4);
                    } elseif ($module_name == "cars") {
                        foreach ($this->car_max_mileage_today_widget->getListItems() as $max_mileage) {
                            $w = new Widget\WidgetObject(["name" => "max_mileage", "options" => json_encode(["car" => $max_mileage])]);
                            $list[] = $this->getWidgetForFrontend($w);
                        }
                        foreach ($this->car_last_refuel_widget->getListItems() as $last_refuel) {
                            $w = new Widget\WidgetObject(["name" => "last_refuel", "options" => json_encode(["car" => $last_refuel])]);
                            $list[] = $this->getWidgetForFrontend($w);
                        }
                    } elseif ($module_name == "splitbills") {
                        foreach ($this->splitted_bills_balance_widget->getListItems() as $splitted_bills_balances) {
                            $w = new Widget\WidgetObject(["name" => "splitted_bills_balances", "options" => json_encode(["group" => $splitted_bills_balances])]);
                            $list[] = $this->getWidgetForFrontend($w);
                        }
                    } elseif ($module_name == "timesheets") {
                        foreach ($this->timesheets_sum_widget->getListItems() as $timesheets_sum) {
                            $w = new Widget\WidgetObject(["name" => "timesheets_sum", "options" => json_encode(["project" => $timesheets_sum])]);
                            $list[] = $this->getWidgetForFrontend($w);
                        }
                    }
                }
            }
        }
        return new Payload(Payload::$RESULT_HTML, ["list" => $list]);
    }

    public function getUserFrontpageEdit()
    {

        $widgets = $this->widget_mapper->getAll('position');
        $list = [];
        foreach ($widgets as $widget_object) {
            $list[] = $this->getWidgetForFrontend($widget_object);
        }

        $available_widgets = [];

        if ($this->current_user->getUser()->hasModule('finances')) {
            $available_widgets["last_finance_entries"] = ["name" => $this->translation->getTranslatedString("LAST_5_EXPENSES")];
            $available_widgets["finances_month_expenses"] = ["name" => $this->translation->getTranslatedString("EXPENSES_THIS_MONTH")];
            $available_widgets["finances_month_income"] = ["name" => $this->translation->getTranslatedString("INCOME_THIS_MONTH")];
            $available_widgets["finances_month_balance"] = ["name" => $this->translation->getTranslatedString("BALANCE_THIS_MONTH")];
        }
        if ($this->current_user->getUser()->hasModule('location')) {
            $available_widgets["steps_today_entries"] = ["name" => $this->translation->getTranslatedString("STEPS_TODAY")];
        }
        if ($this->current_user->getUser()->hasModule('cars')) {
            $available_widgets["max_mileage"] = ["name" => $this->translation->getTranslatedString("REMAINING_KM")];
            $available_widgets["last_refuel"] = ["name" => $this->translation->getTranslatedString("CAR_REFUEL")];
        }
        if ($this->current_user->getUser()->hasModule('splitbills')) {
            $available_widgets["splitted_bills_balances"] = ["name" => $this->translation->getTranslatedString("SPLITBILLS")];
        }
        if ($this->current_user->getUser()->hasModule('timesheets')) {
            $available_widgets["timesheets_sum"] = ["name" => $this->translation->getTranslatedString("TIMESHEETS")];
            $available_widgets["timesheets_project_budget"] = ["name" => $this->translation->getTranslatedString("TIMESHEETS_PROJECT_CATEGORY_BUDGET")];
            $available_widgets["timesheets_fast_create"] = ["name" => $this->translation->getTranslatedString("WIDGET_TIMESHEETS_FAST_CREATE")];
        }
        if ($this->current_user->getUser()->hasModule('boards')) {
            $available_widgets["boards_cards"] = ["name" => $this->translation->getTranslatedString("WIDGET_BOARD_CARDS")];
        }
        if ($this->current_user->getUser()->hasModule('recipes')) {
            $available_widgets["shoppinglist"] = ["name" => $this->translation->getTranslatedString("RECIPES_SHOPPINGLIST")];
        }

        $available_widgets["efa"] = ["name" => $this->translation->getTranslatedString("EFA")];
        $available_widgets["currentweather"] = ["name" => $this->translation->getTranslatedString("WIDGET_CURRENTWEATHER")];
        $available_widgets["weatherforecast"] = ["name" => $this->translation->getTranslatedString("WIDGET_WEATHERFORECAST")];

        return new Payload(Payload::$RESULT_HTML, [
            "widgets" => $available_widgets,
            "list" => $list
        ]);
    }

    private function getWidget($widget_type): Widget\Widget
    {
        switch ($widget_type) {
            case "last_finance_entries":
                return $this->last_finance_entries_widget;
            case "steps_today_entries":
                return $this->steps_today_widget;
            case "finances_month_expenses":
                return $this->finance_month_expenses_widget;
            case "finances_month_income":
                return $this->finance_month_income_widget;
            case "finances_month_balance":
                return $this->finance_month_balance_widget;
            case "last_refuel":
                return $this->car_last_refuel_widget;
            case "max_mileage":
                return $this->car_max_mileage_today_widget;
            case "splitted_bills_balances":
                return $this->splitted_bills_balance_widget;
            case "timesheets_sum":
                return $this->timesheets_sum_widget;
            case "timesheets_project_budget":
                return $this->timesheets_project_budget_widget;
            case "timesheets_fast_create":
                return $this->timesheets_fast_create_widget;
            case "efa":
                return $this->efa_widget;
            case "currentweather":
                return $this->currentweather_widget;
            case "weatherforecast":
                return $this->weatherforecast_widget;
            case "boards_cards":
                return $this->boards_cards_widget;
            case "shoppinglist":
                return $this->shoppinglist_widget;
        }
    }

    public function getWidgetOptions($widget_type, $id)
    {

        $widget_object = $this->widget_mapper->getWidget($id);
        if (is_null($widget_type) && !is_null($widget_object)) {
            $widget_type = $widget_object->name;
        }

        $result = null;
        $widget = $this->getWidget($widget_type);
        $result = $widget->getOptions($widget_object);

        $response_data = ['entry' => $result];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    private function getWidgetForFrontend(Widget\WidgetObject $widget_object, $content = null)
    {

        $options = $widget_object->getOptions();

        $list = [
            "id" => $widget_object->id,
            "name" => $widget_object->name,
            "hasOptions" => $options ? count($options) : false,
            "reload" => 0,
            "content" => !is_null($content) ? $content : '',
            "options" => $widget_object->getOptions()
        ];

        $widget = $this->getWidget($widget_object->name);
        $list["title"] = $widget->getTitle($widget_object);
        $list["url"] = $widget->getLink($widget_object);

        switch ($widget_object->name) {
            case "efa":
                $list["reload"] = 60;
                break;
            case "currentweather":
                $list["reload"] = 3600;
                break;
            case "weatherforecast":
                $list["reload"] = 3600;
                break;
            case "shoppinglist":
                $list["reload"] = 60;
                break;
        }
        return $list;
    }

    public function updatePosition($data)
    {

        if (array_key_exists("widgets", $data) && !empty($data["widgets"])) {
            $widgets = filter_var_array($data["widgets"], FILTER_SANITIZE_NUMBER_INT);
            foreach ($widgets as $position => $item) {
                $this->widget_mapper->updatePosition($item, $position);
            }
            $response_data = ['status' => 'success'];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
        $response_data = ['status' => 'error'];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getWidgetData($id, $widget = null, $options = [])
    {

        $response_data = [];

        if (!is_null($id)) {
            $widget_object = $this->widget_mapper->get($id);
        } else {
            $widget_object = new Widget\WidgetObject(["name" => $widget, "options" => $options]);
        }


        $widget = $this->getWidget($widget_object->name);
        $content = $widget->getContent($widget_object);

        if ($widget_object->name == "efa") {
            $url = $widget_object->getOptions()["url"];
            list($status, $result) = $this->helper->request($url);
            $content = Widget\EFAWidget::formatEFARequestData($status, $result);
        } elseif ($widget_object->name == "currentweather") {
            $url = $widget_object->getOptions()["url"];
            list($status, $result) = $this->helper->request($url);
            $content = Widget\CurrentWeatherWidget::formatCurrentWeatherRequestData($status, $result);
        } elseif ($widget_object->name == "weatherforecast") {
            $url = $widget_object->getOptions()["url"];
            list($status, $result) = $this->helper->request($url);

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

            $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
            $fmtDate->setPattern($dateFormatPHP["weekday"]);

            $content = Widget\WeatherForecastWidget::formatWeatherForecastRequestData($status, $result, $fmtDate);
        }
        $widget_data = $this->getWidgetForFrontend($widget_object, $content);
        $response_data["data"] = $widget_data["content"];
        $response_data["url"] = $widget_data["url"];

        $payload = new Payload(Payload::$RESULT_HTML, $response_data);

        return $payload->withTemplate('home/widgets/' . $widget_object->name . '.twig');
    }
}
