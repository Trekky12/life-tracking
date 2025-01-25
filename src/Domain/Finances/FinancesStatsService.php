<?php

namespace App\Domain\Finances;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Budget\BudgetService;
use App\Application\Payload\Payload;

class FinancesStatsService extends Service {

    private $settings;
    protected $mapper;
    private $cat_service;
    private $budget_service;
    static $GROUP_CATEGORIES_BUDGET_CHART = 5;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            Settings $settings,
            FinancesMapper $mapper,
            CategoryService $cat_service,
            BudgetService $budget_service) {
        parent::__construct($logger, $user);

        $this->settings = $settings;
        $this->mapper = $mapper;
        $this->cat_service = $cat_service;
        $this->budget_service = $budget_service;
    }

    public function statsTotal() {
        $stats = $this->getMapper()->statsTotal();

        list($data, $spendings, $income, $labels, $diff) = $this->createChartData($stats);

        return new Payload(Payload::$RESULT_HTML, [
            'stats' => $data,
            "data1" => $spendings,
            "data2" => $income,
            "labels" => $labels
        ]);
    }

    public function statsYear($year) {
        $stats = $this->getMapper()->statsYear($year);

        list($data, $spendings, $income, $labels, $diff) = $this->createChartData($stats, "month");

        return new Payload(Payload::$RESULT_HTML, [
            'stats' => $data,
            "year" => $year,
            "data1" => $spendings,
            "data2" => $income,
            "labels" => $labels
        ]);
    }

    public function statsYearMonthType($year, $month, $type) {
        $stats = $this->getMapper()->statsMonthType($year, $month, $type);
        list($labels, $data) = $this->preparePieChart($stats);

        return new Payload(Payload::$RESULT_HTML, [
            "stats" => $stats,
            "month" => $month,
            "year" => $year,
            "type" => $type,
            "data" => $data,
            "labels" => $labels
        ]);
    }

    public function statsYearMonthTypeCategory($year, $month, $type, $category) {
        $stats = $this->getMapper()->statsMonthCategory($year, $month, $type, $category);

        $category_name = $this->cat_service->getCategoryName($category);

        list($labels, $data, $count) = $this->preparePieChartGrouped($stats);

        return new Payload(Payload::$RESULT_HTML, [
            "stats" => $stats,
            "month" => $month,
            "year" => $year,
            "type" => $type,
            "category" => $category_name,
            "data" => $data,
            "labels" => $labels
        ]);
    }

    public function statsYearType($year, $type) {
        $stats = $this->getMapper()->statsCategory($year, $type);
        list($labels, $data) = $this->preparePieChart($stats);

        return new Payload(Payload::$RESULT_HTML, [
            "stats" => $stats,
            "type" => $type,
            "year" => $year,
            "data" => $data,
            "labels" => $labels
        ]);
    }

    public function statsYearTypeCategory($year, $type, $category) {
        $stats = $this->getMapper()->statsCategoryDetail($year, $type, $category);

        $category_name = $this->cat_service->getCategoryName($category);

        list($labels, $data, $count) = $this->preparePieChartGrouped($stats);

        return new Payload(Payload::$RESULT_HTML, [
            "stats" => $stats,
            "year" => $year,
            "type" => $type,
            "category" => $category_name,
            "data" => $data,
            "labels" => $labels,
            "count" => $count
        ]);
    }

    public function budget($budget) {
        $budget_name = $this->budget_service->get($budget);

        $is_remains = $this->budget_service->isRemainsBudget($budget);

        if ($is_remains) {
            $stats = $this->getMapper()->statsBudgetRemains();
        } else {
            $stats = $this->getMapper()->statsBudget($budget);
        }

        $categories = $this->budget_service->getCategoriesFromBudget($budget);

        $data = [];

        foreach ($stats as $el) {
            // filter special characters
            // group by category/description
            if (count($categories) >= self::$GROUP_CATEGORIES_BUDGET_CHART || $is_remains) {
                $key = htmlspecialchars_decode($el["category"]);
            } else {
                $key = htmlspecialchars_decode($el["description"]);
            }

            if (!array_key_exists($key, $data)) {
                $data[$key] = 0;
            }
            $data[$key] += $el["value"];
        }


        $labels = json_encode(array_keys($data), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);

        return new Payload(Payload::$RESULT_HTML, [
            "stats" => $stats,
            "budget" => $budget_name->description,
            "data" => $data,
            "labels" => $labels
        ]);
    }

    private function createChartData($stats, $key = "year") {
        $data = [];

        foreach ($stats as $el) {

            if (!array_key_exists($el[$key], $data)) {
                $data[$el[$key]] = [];
            }

            $data[$el[$key]][$el["type"]] = $el["sum"];
        }


        $spendings_data = array_map(function($el) {
            return array_key_exists(0, $el) ? $el[0] : null;
        }, $data);

        $income_data = array_map(function($el) {
            return array_key_exists(1, $el) ? $el[1] : null;
        }, $data);

        $diff_data = array_map(function($el) {
            return array_key_exists(1, $el) && array_key_exists(0, $el) ? $el[1] - $el[0] : null;
        }, $data);

        $labels = array_keys($data);

        if ($key === "month") {
            $labels = array_map(function($l) {
                return DateUtility::getMonthName($this->settings, $l);
            }, $labels);
        }

        $spendings = json_encode(array_values($spendings_data), JSON_NUMERIC_CHECK);
        $income = json_encode(array_values($income_data), JSON_NUMERIC_CHECK);
        $labels = json_encode($labels, JSON_NUMERIC_CHECK);

        $diff = json_encode(array_values($diff_data), JSON_NUMERIC_CHECK);

        return array($data, $spendings, $income, $labels, $diff);
    }

    private function preparePieChart($stats) {
        $labels = array_map(function($e) {
            $cat = htmlspecialchars_decode($e["category"]);
            return $cat;
        }, $stats);
        $data = array_map(function($e) {
            return $e["sum"];
        }, $stats);

        $labels = json_encode(array_values($labels), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);
        return array($labels, $data);
    }

    private function preparePieChartGrouped($stats) {
        $data = [];

        foreach ($stats as $el) {
            // filter special characters
            // group by description
            $cat = htmlspecialchars_decode($el["description"]);

            if (!array_key_exists($cat, $data)) {
                $data[$cat] = 0;
            }
            $data[$cat] += floatval($el["value"]);
        }

        $count = count($data);
        $labels = json_encode(array_keys($data), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);

        return array($labels, $data, $count);
    }

}
