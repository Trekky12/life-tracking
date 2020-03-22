<?php

namespace App\Domain\Location\Steps;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\DateUtility;

class StepsService extends \App\Domain\Service {

    protected $module = "location";
    protected $create_activity = false;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        ;
    }

    public function getStepsPerYear() {
        $steps = $this->mapper->getStepsPerYear();
        list($chart_data, $labels) = $this->createChartData($steps);

        return ['stats' => $steps, "data" => $chart_data, "labels" => $labels];
    }

    public function getStepsOfYear($year) {
        $steps = $this->mapper->getStepsOfYear($year);
        list($chart_data, $labels) = $this->createChartData($steps, "month");

        return ['stats' => $steps, "year" => $year, "data" => $chart_data, "labels" => $labels];
    }

    public function getStepsOfYearMonth($year, $month) {
        $steps = $this->mapper->getStepsOfYearMonth($year, $month);
        list($chart_data, $labels) = $this->createChartData($steps, "date");
        return ['stats' => $steps, "year" => $year, "month" => $month, "data" => $chart_data, "labels" => $labels];
    }

    public function getStepsOfDate($date) {
        return $this->mapper->getStepsOfDate($date);
    }

    private function createChartData($stats, $key = "year") {
        $data = [];

        foreach ($stats as $el) {
            if (!array_key_exists($el[$key], $data)) {
                $data[$el[$key]] = [];
            }

            $data[$el[$key]] = $el["steps"];
        }

        $labels = array_keys($data);
        if ($key === "month") {
            $labels = array_map(function($l) {
                return DateUtility::getMonthName($this->settings, $l);
            }, $labels);
        }
        if ($key === "date") {
            $labels = array_map(function($l) {
                return DateUtility::getDay($this->settings, $l);
            }, $labels);
        }

        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);
        $labels = json_encode($labels, JSON_NUMERIC_CHECK);

        return array($data, $labels);
    }

    public function updateSteps($date, $steps_new) {
        $steps_old = $this->mapper->getStepsOfDate($date);

        // update
        if ($steps_old > 0) {
            $this->mapper->updateSteps($date, $steps_old, $steps_new);
        }
        // insert
        else {
            $this->mapper->insertSteps($date, $steps_new);
        }
    }

}
