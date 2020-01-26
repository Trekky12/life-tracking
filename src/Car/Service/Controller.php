<?php

namespace App\Car\Service;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $module = "cars";
    protected $model = '\App\Car\Service\CarServiceEntry';
    protected $parent_model = '\App\Car\Car';
    protected $index_route = 'car_service';
    protected $element_view_route = 'car_service_edit';
    private $car_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->car_mapper = new \App\Car\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);

        $fuel_list = $this->mapper->tableDataFuel($user_cars, 'date', 'DESC', 10);
        $fuel_table = $this->renderFuelTableRows($fuel_list);
        $fuel_datacount = $this->mapper->tableCount($user_cars, 0);

        $service_list = $this->mapper->tableDataService($user_cars, 'date', 'DESC', 10);
        $service_table = $this->renderServiceTableRows($service_list);
        $service_datacount = $this->mapper->tableCount($user_cars, 1);

        $cars = $this->car_mapper->getAll();

        return $this->ci->view->render($response, 'cars/service/index.twig', [
                    'fuel_table' => $fuel_table,
                    'datacount' => $fuel_datacount,
                    'cars' => $cars,
                    'service_table' => $service_table,
                    'datacount2' => $service_datacount
                        ]
        );
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        // GET Param 'type'
        $type = $request->getParam('type');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $cars = $this->car_mapper->getAll('name');

        $this->preEdit($entry_id, $request);

        return $this->ci->view->render($response, 'cars/service/edit.twig', ['entry' => $entry, 'cars' => $cars, 'user_cars' => $user_cars, 'type' => $type]);
    }

    protected function afterSave($id, array $data, Request $request) {

        $entry = $this->mapper->get($id);


        /**
         * Set Distance
         */
        if ($entry->mileage) {
            $lastMileage = $this->mapper->getLastMileage($id, $entry->mileage, $entry->car);
            if ($lastMileage) {
                $this->mapper->setDistance($id, $lastMileage);
            }
        }

        /**
         * Reset if set
         */
        $this->mapper->setConsumption($id, null);

        /**
         * Calculate Consumption when full
         */
        if ($entry->mileage && $entry->fuel_calc_consumption && $entry->fuel_type == 1 && $lastMileage) {

            $lastFull = $this->mapper->getLastFull($id, $entry->mileage, $entry->car);
            if ($lastFull) {

                $distance = $entry->mileage - $lastFull->mileage;
                $volume = $this->mapper->getVolume($entry->car, $entry->date, $lastFull->date);
                $consumption = ($volume / $distance) * 100;

                $this->mapper->setConsumption($id, $consumption);
            }
        }
    }

    public function stats(Request $request, Response $response) {
        //$list = $this->mapper->getAll('date ASC');

        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $list = $this->mapper->getAllofCars('date ASC, mileage ASC', false, $user_cars);

        $cars = $this->car_mapper->getAll();


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
        $calculation_type = $this->ci->get('helper')->getSessionVar('mileage_type', 0);

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

        return $this->ci->view->render($response, 'cars/stats.twig', [
                    'data' => $data,
                    "labels" => json_encode($labels),
                    "table" => $table,
                    "cars" => $cars,
                    "totalMileages" => $totalMileages,
                    "mileage_calc_type" => $calculation_type,
                    "mileage_year" => $mileage_year
        ]);
    }

    public function setYearlyMileageCalcTyp(Request $request, Response $response) {
        $data = $request->getParsedBody();

        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1, 2))) {
            $this->ci->get('helper')->setSessionVar('mileage_type', $data["state"]);
        }

        return $response->withJSON(array('status' => 'success'));
    }

    public function tableFuel(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);

        $recordsTotal = $this->mapper->countwithCars($user_cars);
        $recordsFiltered = $this->mapper->tableCount($user_cars, 0, $searchQuery);

        $lang = [0 => $this->ci->get('helper')->getTranslatedString("FUEL_PARTLY"), 1 => $this->ci->get('helper')->getTranslatedString("FUEL_FULL")];

        $data = $this->mapper->tableDataFuel($user_cars, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderFuelTableRows($data);

        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => $table
                        ]
        );
    }

    public function tableService(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);

        $recordsTotal = $this->mapper->countwithCars($user_cars, 1);
        $recordsFiltered = $this->mapper->tableCount($user_cars, 1, $searchQuery);

        $data = $this->mapper->tableDataService($user_cars, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderServiceTableRows($data);

        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => $table
                        ]
        );
    }

    private function renderFuelTableRows(array $table) {
        foreach ($table as &$row) {
            $row[9] = '<a href="' . $this->ci->get('router')->pathFor('car_service_edit', ['id' => $row[9]]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[10] = '<a href="#" data-url="' . $this->ci->get('router')->pathFor('car_service_delete', ['id' => $row[10]]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>';
        }
        return $table;
    }

    private function renderServiceTableRows(array $table) {
        foreach ($table as &$row) {
            $row[8] = '<a href="' . $this->ci->get('router')->pathFor('car_service_edit', ['id' => $row[8]]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[9] = '<a href="#" data-url="' . $this->ci->get('router')->pathFor('car_service_delete', ['id' => $row[9]]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>';
        }
        return $table;
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preEdit($id, Request $request) {

        if (!is_null($id)) {
            $entry = $this->mapper->get($id);
            $user = $this->ci->get('helper')->getUser()->id;
            $user_cars = $this->car_mapper->getElementsOfUser($user);
            if (!in_array($entry->car, $user_cars)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        if (!array_key_exists("car", $data) || !in_array($data["car"], $user_cars)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preDelete($id, Request $request) {
        if (!is_null($id)) {
            $entry = $this->mapper->get($id);
            $user = $this->ci->get('helper')->getUser()->id;
            $user_cars = $this->car_mapper->getElementsOfUser($user);
            if (!in_array($entry->car, $user_cars)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    protected function getParentObjectMapper() {
        return $this->car_mapper;
    }

}
