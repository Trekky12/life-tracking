<?php

namespace App\Domain\Car\Service;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Car\CarService;
use App\Application\Payload\Payload;

class CarServiceStatsService extends Service {

    private $car_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CarServiceMapper $mapper, CarService $car_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->car_service = $car_service;
    }

    public function stats($hash) {

        $car = $this->car_service->getFromHash($hash);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        /**
         * Chart
         */
        $list = $this->mapper->getAllOfType($car->id, 'date ASC, mileage ASC', false);
        $data = [];
        $labels = [];
        $raw_data = [];
        foreach ($list as $el) {
            if (!empty($el->refill_consumption) && !empty($el->date)) {
                $raw_data[] = array("label" => $el->date, "car" => $el->car, "consumption" => $el->refill_consumption);
            }
        }

        foreach ($raw_data as $idx => $el) {
            $labels[] = $el["label"];
            $data[] = $el["consumption"];
        }

        /**
         * Mileage Table
         */

        // Get intervals
        $minMileage = $this->mapper->minMileage($car->id);

        // Get Calculation type
        $calculation_type = SessionUtility::getSessionVar('mileage_type', 0);
        // Start Date
        if (intval($calculation_type) === 0) {
            $mindate = !is_null($car->mileage_start_date) ? $car->mileage_start_date : $minMileage["date"];
            // First Entry
        } elseif (intval($calculation_type) === 1) {
            $mindate = $minMileage["date"];
            // 01.01.
        } else {
            $date = \DateTime::createFromFormat("Y-m-d", $minMileage["date"]);
            $date->modify('first day of january ' . $date->format('Y'));
            $mindate = $date->format("Y-m-d");
        }

        /**
         * Table Data
         */
        $table = [];
        $last_mileage = $car->mileage_start;
        $terms = $car->mileage_term;
        $current_date = new \DateTime('now');
        $i = 0;
        do {
            $miledata = $this->mapper->sumMileageInterval($car->id, $mindate);

            // calculate diff 
            $miledata["diff"] = $miledata["max"] - $last_mileage;

            $table[] = $miledata;

            // this end date is new min date
            $mindate = $miledata["end"];
            $last_mileage = $miledata["max"];

            $i++;
        } while ($i < $terms && new \DateTime($mindate) <= $current_date);

        /**
         * Calculate per term with specific start date
         */
        $mileage_year = $this->getAllowedMileage($car);

        return new Payload(Payload::$RESULT_HTML, [
            'data' => $data,
            "labels" => json_encode($labels),
            "table" => $table,
            "mileage_calc_type" => $calculation_type,
            "mileage_year" => $mileage_year,
            "car" => $car
        ]);
    }

    public function getAllowedMileage($car) {
        $totalMileageWithStartDate = $this->mapper->getTotalMileage($car->id, true);
        $current_mileage_year =  $totalMileageWithStartDate["diff"];

        $current_date = new \DateTime('now');
        $year_start = new \DateTime($car->mileage_start_date ?? '');
        $year_end = clone $year_start;
        if (!is_null($car->mileage_term)) {
            $year_end->add(new \DateInterval('P' . $car->mileage_term . 'Y'));
        }
        $max_mileage = $car->mileage_per_year * $car->mileage_term;

        $is_in_interval = $current_date >= $year_start && $current_date <= $year_end;

        if ($is_in_interval && !is_null($max_mileage) && !is_null($current_mileage_year)) {
            // maybe it is a leap year
            $days_of_year = $year_start->diff($year_end)->days;
            // days since start
            $current_day_of_year = $year_start->diff($current_date)->days;

            $possible_mileage_today = round($current_day_of_year / $days_of_year * $max_mileage);

            return ["possible" => $possible_mileage_today, "remaining" => $possible_mileage_today - $current_mileage_year, "current" => $current_mileage_year];
        }

        return null;
    }
}
