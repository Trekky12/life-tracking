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
use App\Domain\Home\Widget\WidgetMapper;
use App\Domain\Base\Settings;
use App\Domain\Main\Translator;
use App\Domain\Main\Helper;
use App\Domain\Home\Widget\EFAWidget;
use App\Domain\Home\Widget\CurrentWeatherWidget;
use App\Domain\Home\Widget\WeatherForecastWidget;

class HomeService extends Service {

    private $translation;
    private $settings;
    private $helper;
    private $last_finance_entries_widget;
    private $steps_today_widget;
    private $finance_month_expenses_widget;
    private $finance_month_income_widget;
    private $finance_month_balance_widget;
    private $car_max_mileage_today_widget;
    private $splitted_bills_balance_widget;
    private $timesheets_sum_widget;
    private $efa_widget;
    private $currentweather_widget;
    private $weatherforecast_widget;
    private $widget_mapper;

    public function __construct(LoggerInterface $logger,
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
            EFAWidget $efa_widget,
            CurrentWeatherWidget $currentweather_widget,
            WeatherForecastWidget $weatherforecast_widget,
            WidgetMapper $widget_mapper) {
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
        $this->efa_widget = $efa_widget;
        $this->currentweather_widget = $currentweather_widget;
        $this->weatherforecast_widget = $weatherforecast_widget;

        $this->widget_mapper = $widget_mapper;
    }

    public function getUserStartPage($pwa) {
        // is PWA? redirect to start page
        $user = $this->current_user->getUser();
        if (!is_null($pwa) && !is_null($user) && !empty($user->start_url)) {
            return new Payload(Payload::$STATUS_HAS_START_URL, $user->start_url);
        }

        $widgets = $this->widget_mapper->getAll('position');
        $list = [];
        if (count($widgets) > 0) {
            foreach ($widgets as $widget) {
                $list[] = $this->getWidgetForFrontend($widget);
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

    public function getUserFrontpageEdit() {

        $widgets = $this->widget_mapper->getAll('position');
        $list = [];
        foreach ($widgets as $widget) {
            $list[] = $this->getWidgetForFrontend($widget);
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
        }

        $available_widgets["efa"] = ["name" => $this->translation->getTranslatedString("EFA")];
        $available_widgets["currentweather"] = ["name" => $this->translation->getTranslatedString("WIDGET_CURRENTWEATHER")];
        $available_widgets["weatherforecast"] = ["name" => $this->translation->getTranslatedString("WIDGET_WEATHERFORECAST")];

        return new Payload(Payload::$RESULT_HTML, [
            "widgets" => $available_widgets,
            "list" => $list
        ]);
    }

    public function getWidgetOptions($widget_type, $id) {

        $widget = $this->widget_mapper->getWidget($id);
        if (is_null($widget_type) && !is_null($widget)) {
            $widget_type = $widget->name;
        }

        $result = null;
        switch ($widget_type) {
            case "max_mileage":
                $result = $this->car_max_mileage_today_widget->getOptions($widget);
                break;
            case "last_refuel":
                $result = $this->car_last_refuel_widget->getOptions($widget);
                break;
            case "splitted_bills_balances":
                $result = $this->splitted_bills_balance_widget->getOptions($widget);
                break;
            case "timesheets_sum":
                $result = $this->timesheets_sum_widget->getOptions($widget);
                break;
            case "efa":
                $result = $this->efa_widget->getOptions($widget);
                break;
            case "currentweather":
                $result = $this->currentweather_widget->getOptions($widget);
                break;
            case "weatherforecast":
                $result = $this->weatherforecast_widget->getOptions($widget);
                break;
            default:
                $result = null;
                break;
        }

        $response_data = ['entry' => $result];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    private function getWidgetForFrontend(Widget\WidgetObject $widget) {

        $options = $widget->getOptions();

        $list = [
            "id" => $widget->id,
            "name" => $widget->name,
            "hasOptions" => $options ? count($options) : false
        ];
        switch ($widget->name) {
            case "last_finance_entries":
                $list["title"] = $this->last_finance_entries_widget->getTitle();
                $list["content"] = $this->last_finance_entries_widget->getContent();
                $list["url"] = $this->last_finance_entries_widget->getLink();
                break;
            case "steps_today_entries":
                $list["title"] = $this->steps_today_widget->getTitle();
                $list["content"] = $this->steps_today_widget->getContent();
                $list["url"] = $this->steps_today_widget->getLink();
                break;
            case "finances_month_expenses":
                $list["title"] = $this->finance_month_expenses_widget->getTitle();
                $list["content"] = $this->finance_month_expenses_widget->getContent();
                $list["url"] = $this->finance_month_expenses_widget->getLink();
                break;
            case "finances_month_income":
                $list["title"] = $this->finance_month_income_widget->getTitle();
                $list["content"] = $this->finance_month_income_widget->getContent();
                $list["url"] = $this->finance_month_income_widget->getLink();
                break;
            case "finances_month_balance":
                $list["title"] = $this->finance_month_balance_widget->getTitle();
                $list["content"] = $this->finance_month_balance_widget->getContent();
                $list["url"] = $this->finance_month_balance_widget->getLink();
                break;
            case "last_refuel":
                $list["title"] = $this->car_last_refuel_widget->getTitle($widget);
                $list["content"] = $this->car_last_refuel_widget->getContent($widget);
                $list["url"] = $this->car_last_refuel_widget->getLink($widget);
                break;
            case "max_mileage":
                $list["title"] = $this->car_max_mileage_today_widget->getTitle($widget);
                $list["content"] = $this->car_max_mileage_today_widget->getContent($widget);
                $list["url"] = $this->car_max_mileage_today_widget->getLink($widget);
                break;
            case "splitted_bills_balances":
                $list["title"] = $this->splitted_bills_balance_widget->getTitle($widget);
                $list["content"] = $this->splitted_bills_balance_widget->getContent($widget);
                $list["url"] = $this->splitted_bills_balance_widget->getLink($widget);
                break;
            case "timesheets_sum":
                $list["title"] = $this->timesheets_sum_widget->getTitle($widget);
                $list["content"] = $this->timesheets_sum_widget->getContent($widget);
                $list["url"] = $this->timesheets_sum_widget->getLink($widget);
                break;
            case "efa":
                $list["title"] = $this->efa_widget->getTitle($widget);
                $list["content"] = $this->efa_widget->getContent($widget);
                $list["url"] = $this->efa_widget->getLink($widget);
                break;
            case "currentweather":
                $list["title"] = $this->currentweather_widget->getTitle($widget);
                $list["content"] = $this->currentweather_widget->getContent($widget);
                $list["url"] = $this->currentweather_widget->getLink($widget);
                break;
            case "weatherforecast":
                $list["title"] = $this->weatherforecast_widget->getTitle($widget);
                $list["content"] = $this->weatherforecast_widget->getContent($widget);
                $list["url"] = $this->weatherforecast_widget->getLink($widget);
                break;
        }
        return $list;
    }

    public function updatePosition($data) {

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

    public function getWidgetRequestData($id) {

        $widget = $this->widget_mapper->get($id);

        $url = $widget->getOptions()["url"];
        $type = $widget->name;

        list($status, $result) = $this->helper->request($url);

        $response_data = ['status' => $status, "result" => $result, "type" => $type];

        if ($status == 200) {

            if ($type == "efa") {
                libxml_use_internal_errors(true);
                $xml_result = [];
                $xml = simplexml_load_string($result);

                if ($xml !== false) {
                    foreach ($xml->itdDepartureMonitorRequest[0]->itdDepartureList[0]->itdDeparture as $departure) {
                        $dep = [];

                        $dep["servingLine"]["number"] = (string) $departure->itdServingLine->attributes()->number;
                        $dep["servingLine"]["direction"] = (string) $departure->itdServingLine->attributes()->direction;
                        $dep["servingLine"]["delay"] = (int) $departure->itdServingLine->itdNoTrain->attributes()->delay;

                        $dep["dateTime"]["year"] = (string) $departure->itdDateTime->itdDate->attributes()->year;
                        $dep["dateTime"]["month"] = (string) $departure->itdDateTime->itdDate->attributes()->month;
                        $dep["dateTime"]["day"] = (string) $departure->itdDateTime->itdDate->attributes()->day;
                        $dep["dateTime"]["hour"] = (string) $departure->itdDateTime->itdTime->attributes()->hour;
                        $dep["dateTime"]["minute"] = (string) $departure->itdDateTime->itdTime->attributes()->minute;

                        if (isset($departure->itdRTDateTime)) {
                            $dep["realDateTime"]["year"] = (string) $departure->itdRTDateTime->itdDate->attributes()->year;
                            $dep["realDateTime"]["month"] = (string) $departure->itdRTDateTime->itdDate->attributes()->month;
                            $dep["realDateTime"]["day"] = (string) $departure->itdRTDateTime->itdDate->attributes()->day;
                            $dep["realDateTime"]["hour"] = (string) $departure->itdRTDateTime->itdTime->attributes()->hour;
                            $dep["realDateTime"]["minute"] = (string) $departure->itdRTDateTime->itdTime->attributes()->minute;
                        }

                        $xml_result["departureList"][] = $dep;
                    }
                }

                $result = json_encode($xml_result);
            }

            // Convert unicode 
            // @see https://stackoverflow.com/a/2577882
            $result = mb_convert_encoding($result, 'HTML-ENTITIES');

            $response_data["result"] = json_decode($result, true);
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
