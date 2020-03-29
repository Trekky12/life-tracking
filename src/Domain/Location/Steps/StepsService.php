<?php

namespace App\Domain\Location\Steps;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\DateUtility;
use App\Application\Payload\Payload;

class StepsService extends GeneralService {

    private $settings;

    public function __construct(LoggerInterface $logger, CurrentUser $user, Settings $settings, StepsMapper $mapper) {
        parent::__construct($logger, $user);
        $this->settings = $settings;
        $this->mapper = $mapper;
    }

    public function getStepsPerYear() {
        $steps = $this->mapper->getStepsPerYear();
        list($chart_data, $labels) = $this->createChartData($steps);

        return new Payload(Payload::$RESULT_HTML, ['stats' => $steps, "data" => $chart_data, "labels" => $labels]);
    }

    public function getStepsOfYear($year) {
        $steps = $this->mapper->getStepsOfYear($year);
        list($chart_data, $labels) = $this->createChartData($steps, "month");

        return new Payload(Payload::$RESULT_HTML, ['stats' => $steps, "year" => $year, "data" => $chart_data, "labels" => $labels]);
    }

    public function getStepsOfYearMonth($year, $month) {
        $steps = $this->mapper->getStepsOfYearMonth($year, $month);
        list($chart_data, $labels) = $this->createChartData($steps, "date");
        return new Payload(Payload::$RESULT_HTML, ['stats' => $steps, "year" => $year, "month" => $month, "data" => $chart_data, "labels" => $labels]);
    }

    public function getStepsOfDate($date) {
        $steps = $this->mapper->getStepsOfDate($date);
        return new Payload(Payload::$RESULT_HTML, ['date' => $date, 'steps' => $steps > 0 ? $steps : 0]);
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

    private function updateSteps($date, $steps_new) {
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

    public function saveSteps($date, $data) {
        $steps_new = array_key_exists("steps", $data) ? filter_var($data["steps"], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->updateSteps($date, $steps_new);

        $dateObj = new \DateTime($date);
        $data = ['year' => $dateObj->format('Y'), 'month' => $dateObj->format('m')];
        return $data;
    }

}
