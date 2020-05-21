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
use App\Domain\Home\Widget\CarMaxMileageTodayWidget;
use App\Domain\Home\Widget\CarLastRefuelWidget;
use App\Domain\Home\Widget\SplittedBillsBalanceWidget;
use App\Domain\Home\Widget\TimesheetsSumWidget;

class HomeService extends Service {

    private $last_finance_entries_widget;
    private $steps_today_widget;
    private $finance_month_expenses_widget;
    private $finance_month_income_widget;
    private $car_max_mileage_today_widget;
    private $splitted_bills_balance_widget;
    private $timesheets_sum_widget;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            LastFinanceEntriesWidget $last_finance_entries_widget,
            StepsTodayWidget $steps_today_widget,
            FinanceMonthExpensesWidget $finance_month_expenses_widget,
            FinanceMonthIncomeWidget $finance_month_income_widget,
            CarMaxMileageTodayWidget $car_max_mileage_today_widget,
            CarLastRefuelWidget $car_last_refuel_widget,
            SplittedBillsBalanceWidget $splitted_bills_balance_widget,
            TimesheetsSumWidget $timesheets_sum_widget) {
        parent::__construct($logger, $user);
        $this->last_finance_entries_widget = $last_finance_entries_widget;
        $this->steps_today_widget = $steps_today_widget;
        $this->finance_month_expenses_widget = $finance_month_expenses_widget;
        $this->finance_month_income_widget = $finance_month_income_widget;
        $this->car_max_mileage_today_widget = $car_max_mileage_today_widget;
        $this->car_last_refuel_widget = $car_last_refuel_widget;
        $this->splitted_bills_balance_widget = $splitted_bills_balance_widget;
        $this->timesheets_sum_widget = $timesheets_sum_widget;
    }

    public function getUserStartPage($pwa) {
        // is PWA? redirect to start page
        $user = $this->current_user->getUser();
        if (!is_null($pwa) && !is_null($user) && !empty($user->start_url)) {
            return new Payload(Payload::$STATUS_HAS_START_URL, $user->start_url);
        }

        $last_finance_entries = $this->last_finance_entries_widget->getContent();
        $steps_today_entries = $this->steps_today_widget->getContent();
        $finances_month_expenses = $this->finance_month_expenses_widget->getContent();
        $finances_month_income = $this->finance_month_income_widget->getContent();
        $max_mileage = $this->car_max_mileage_today_widget->getContent();
        $last_refuel = $this->car_last_refuel_widget->getContent();
        $splitted_bills_balances = $this->splitted_bills_balance_widget->getContent();
        $timesheets_sum = $this->timesheets_sum_widget->getContent();

        return new Payload(Payload::$RESULT_HTML, [
            "last_finance_entries" => $last_finance_entries,
            "steps_today_entries" => $steps_today_entries,
            "finances_month_expenses" => $finances_month_expenses,
            "finances_month_income" => $finances_month_income,
            "max_mileage" => $max_mileage,
            "last_refuel" => $last_refuel,
            "splitted_bills_balances" => $splitted_bills_balances,
            "timesheets_sum" => $timesheets_sum
        ]);
    }

}
