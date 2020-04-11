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

    public function stats() {
        $user_cars = $this->car_service->getUserCars();
        $cars = $this->car_service->getAllCarsOrderedByName();

        $list = $this->mapper->getAllofCars('date ASC, mileage ASC', false, $user_cars);


        /**
         * Create Labels
         */
        $data = [];
        $labels = [];
        $raw_data = [];
        foreach ($list as $el) {
            if (!empty($el->fuel_consumption) && !empty($el->date)) {
                $raw_data[] = array("label" => $el->date, "car" => $el->car, "consumption" => $el->fuel_consumption);
            }
        }
        /**
         * Fill each array for each cars with null
         */
        foreach ($user_cars as $uc) {
            $data[$uc]["data"] = array_fill(0, count($raw_data), null);
            $car = addslashes(htmlspecialchars_decode($cars[$uc]->name));
            $data[$uc]["name"] = $car;
        }
        /**
         * Replace null values with correct values at corresponding positions
         */
        foreach ($raw_data as $idx => $el) {
            $labels[] = $el["label"];
            $data[$el["car"]]["data"][$idx] = $el["consumption"];
        }

        // Get intervals
        $minMileages = $this->mapper->minMileage();

        // Get total distance
        $totalMileages = $this->mapper->getTotalMileage();
        $totalMileagesWithStartDate = $this->mapper->getTotalMileage(true);

        $table = [];

        // Get Calculation type
        $calculation_type = SessionUtility::getSessionVar('mileage_type', 0);

        $mileage_year = [];

        foreach ($minMileages as $car => $min) {
            // is allowed?
            if (in_array($car, $user_cars)) {

                if (!array_key_exists($car, $table)) {
                    $table[$car] = array();
                }

                if (intval($calculation_type) === 0) {
                    $mindate = !is_null($cars[$car]->mileage_start_date) ? $cars[$car]->mileage_start_date : $min["date"];
                } elseif (intval($calculation_type) === 1) {
                    $mindate = $min["date"];
                } else {
                    $date = \DateTime::createFromFormat("Y-m-d", $min["date"]);
                    $date->modify('first day of january ' . $date->format('Y'));
                    $mindate = $date->format("Y-m-d");
                }

                /**
                 * Table Data
                 */
                $last_mileage = $min["mileage"];
                $diff = 0;
                do {
                    $miledata = $this->mapper->sumMileageInterval($car, $mindate);

                    // calculate diff 
                    $diff = $miledata["max"] - $last_mileage;
                    $miledata["diff"] = $diff;

                    if ($miledata["diff"] > 0) {
                        $table[$car][] = $miledata;
                    }

                    // this end date is new min date
                    $mindate = $miledata["end"];
                    $last_mileage = $miledata["max"];
                } while ($diff > 0);

                /**
                 * Get Mileage per Year
                 */
                // Get first element in the array => recent year
                $recent_year = end($table[$car]);
                $current_date = new \DateTime('now');

                /**
                 * Calculate only per year
                 */
                // $year_start = new \DateTime($recent_year["start"]);
                // $year_end = new \DateTime($recent_year["end"]);
                // $max_mileage_year = $cars[$car]->mileage_per_year;
                // $current_mileage_year = $recent_year["diff"];

                /**
                 * Calculate per term with specific start date
                 */
                $year_start = new \DateTime($cars[$car]->mileage_start_date);
                $year_end = clone $year_start;
                if (!is_null($cars[$car]->mileage_term)) {
                    $year_end->add(new \DateInterval('P' . $cars[$car]->mileage_term . 'Y'));
                }
                $max_mileage = $cars[$car]->mileage_per_year * $cars[$car]->mileage_term;
                $current_mileage_year = array_key_exists($car, $totalMileagesWithStartDate) ? $totalMileagesWithStartDate[$car]["diff"] : null;

                $is_in_interval = $current_date >= $year_start && $current_date <= $year_end;

                if ($is_in_interval && !is_null($max_mileage) && !is_null($current_mileage_year)) {
                    // maybe it is a leap year
                    $days_of_year = $year_start->diff($year_end)->days;
                    // days since start
                    $current_day_of_year = $year_start->diff($current_date)->days;

                    $possible_mileage_today = round($current_day_of_year / $days_of_year * $max_mileage);

                    $mileage_year[$car] = ["possible" => $possible_mileage_today, "remaining" => $possible_mileage_today - $current_mileage_year, "current" => $current_mileage_year];
                }
            }
        }

        return new Payload(Payload::$RESULT_HTML, [
            'data' => $data,
            "labels" => json_encode($labels),
            "table" => $table,
            "cars" => $cars,
            "totalMileages" => $totalMileages,
            "mileage_calc_type" => $calculation_type,
            "mileage_year" => $mileage_year
        ]);
    }

}
