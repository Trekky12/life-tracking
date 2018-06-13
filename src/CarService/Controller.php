<?php

namespace App\CarService;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $car_mapper;

    public function init() {
        $this->model = '\App\CarService\CarServiceEntry';
        $this->index_route = 'car_service';

        $this->mapper = new \App\CarService\Mapper($this->ci);
        $this->car_mapper = new \App\Car\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);

        $fuel_table = $this->mapper->getAllofCars('date DESC', 10, $user_cars);
        $fuel_datacount = $this->mapper->countwithCars($user_cars);

        $service_table = $this->mapper->getAllofCars('date DESC', 10, $user_cars, 1);
        $service_datacount = $this->mapper->countwithCars($user_cars, 1);

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

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $cars = $this->car_mapper->getAll('name');

        $this->preEdit($entry_id);

        return $this->ci->view->render($response, 'cars/service/edit.twig', ['entry' => $entry, 'cars' => $cars, 'user_cars' => $user_cars]);
    }

    protected function afterSave($id, $data) {

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
            $data[$uc]["name"] = $cars[$uc]->name;
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

        $table = [];

        // Get Calculation type
        $calculation_type = $this->ci->get('helper')->getSessionVar('mileage_type', 0);


        foreach ($minMileages as $car => $min) {
            // is allowed?
            if (in_array($car, $user_cars)) {

                if (!array_key_exists($car, $table)) {
                    $table[$car] = array();
                }

                if (intval($calculation_type) === 0) {
                    $mindate = $min["date"];
                } else {
                    $date = \DateTime::createFromFormat("Y-m-d", $min["date"]);
                    $date->modify('first day of january ' . $date->format('Y'));
                    $mindate = $date->format("Y-m-d");
                }

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
            }
        }
        return $this->ci->view->render($response, 'cars/stats.twig', ['data' => $data, "labels" => json_encode($labels), "table" => $table, "cars" => $cars, "totalMileages" => $totalMileages, "mileage_calc_type" => $calculation_type]);
    }

    public function setYearlyMileageCalcTyp(Request $request, Response $response) {
        $data = $request->getParsedBody();

        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1))) {
            $this->ci->get('helper')->setSessionVar('mileage_type', $data["state"]);
        }

        return $response->withJSON(array('status' => 'success'));
    }

    public function tableFuel(Request $request, Response $response) {


        $requestData = $request->getQueryParams();
        $cars = $this->car_mapper->getAll();

        $columns = array(
            array('db' => 'date', 'dt' => 0),
            array(
                'db' => 'car',
                'dt' => 1,
                'formatter' => function( $d, $row ) use ($cars) {
                    return $cars[$d]->name;
                }
            ),
            array('db' => 'mileage', 'dt' => 2),
            array('db' => 'fuel_price', 'dt' => 3),
            array('db' => 'fuel_volume', 'dt' => 4),
            array('db' => 'fuel_total_price', 'dt' => 5),
            array(
                'db' => 'fuel_type',
                'dt' => 6,
                'formatter' => function( $d, $row ) {
                    if ($row["fuel_volume"] <= 0) {
                        return '';
                    }
                    return $d == 0 ? $this->ci->get('helper')->getTranslatedString("FUEL_PARTLY") : $this->ci->get('helper')->getTranslatedString("FUEL_FULL");
                }
            ),
            array('db' => 'fuel_consumption', 'dt' => 7),
            array('db' => 'fuel_location', 'dt' => 8),
            //array('db' => 'notice', 'dt' => 9),
            array(
                'db' => 'id',
                'dt' => 9,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('car_service_edit', ['id' => $d]);
                    return '<a href="' . $link . '"><span class="fa fa-pencil-square-o fa-lg"></span></a>';
                }
            ),
            array(
                'db' => 'id',
                'dt' => 10,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('car_service_delete', ['id' => $d]);
                    return '<a href="#" data-url="' . $link . '" class="btn-delete"><span class="fa fa-trash fa-lg"></span></a>';
                }
            )
        );


        $user = $this->ci->get('helper')->getUser()->id;
        //$whereUser = $user ? "(user = {$user} OR user IS NULL)" : "";

        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $where = !empty($user_cars) ? "(car IN (" . implode(',', $user_cars) . "))" : " 1!=1";

        $where .= (!empty($where) ? " AND " : "" ) . " type = 0 ";


        /**
         * @see https://github.com/DataTables/DataTablesSrc/blob/master/examples/server_side/scripts/ssp.class.php
         */
        $pdo = $this->ci->get('db');
        $data = \App\Main\SSP::complex($requestData, $pdo, "cars_service", "id", $columns, null, $where);
        return $response->withJson($data);
    }

    public function tableService(Request $request, Response $response) {


        $requestData = $request->getQueryParams();
        $cars = $this->car_mapper->getAll();

        $columns = array(
            array('db' => 'date', 'dt' => 0),
            array(
                'db' => 'car',
                'dt' => 1,
                'formatter' => function( $d, $row ) use ($cars) {
                    return $cars[$d]->name;
                }
            ),
            array('db' => 'mileage', 'dt' => 2),
            array('db' => 'service_oil_before', 'dt' => 3),
            array('db' => 'service_water_wiper_before', 'dt' => 4),
            array('db' => 'service_air_front_left_before', 'dt' => 5),
            array('db' => 'service_tire_change', 'dt' => 6),
            array('db' => 'service_garage', 'dt' => 7),
            array(
                'db' => 'id',
                'dt' => 8,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('car_service_edit', ['id' => $d]);
                    return '<a href="' . $link . '"><span class="fa fa-pencil-square-o fa-lg"></span></a>';
                }
            ),
            array(
                'db' => 'id',
                'dt' => 9,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('car_service_delete', ['id' => $d]);
                    return '<a href="#" data-url="' . $link . '" class="btn-delete"><span class="fa fa-trash fa-lg"></span></a>';
                }
            )
        );


        $user = $this->ci->get('helper')->getUser()->id;

        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $whereAll = !empty($user_cars) ? "(car IN (" . implode(',', $user_cars) . "))" : " 1!=1";
        $whereAll .= (!empty($whereAll) ? " AND " : "" ) . " type = 1 ";


        //$pdo = $this->ci->get('db');
        //$data = \App\Main\SSP::complex($requestData, $pdo, "cars_service", "id", $columns, null, $whereAll);
        //return $response->withJson($data);

        $bindings = array();

        $limit = \App\Main\SSP::limit($requestData, $columns);
        $order = \App\Main\SSP::order($requestData, $columns);
        $where = \App\Main\SSP::filter($requestData, $columns, $bindings);

        // concat $where and $whereAll
        $where = $where ? $where . ' AND ' . $whereAll : 'WHERE ' . $whereAll;
        $whereAllSql = 'WHERE ' . $whereAll;


        $raw_data = $this->mapper->dataTableService($where, $bindings, $order, $limit);
        $data = array();
        foreach ($raw_data as $row) {
            $my_row = array(
                "date" => $row->date,
                "car" => $row->car,
                "mileage" => $row->mileage,
                "id" => $row->id,
                "service_oil_before" => $row->isServiceOil() ? 'x' : '',
                "service_water_wiper_before" => $row->isServiceWaterWiper() ? 'x' : '',
                "service_air_front_left_before" => $row->isServiceAir() ? 'x' : '',
                "service_tire_change" => $row->isServiceTireChange() ? 'x' : '',
                "service_garage" => $row->isServiceGarage() ? 'x' : '',
            );
            $data[] = $my_row;
        }

        $recordsFiltered = $this->mapper->dataTableServiceCount($whereAllSql, $bindings);
        $recordsTotal = $this->mapper->count();

        return $response->withJson([
                    "draw" => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => \App\Main\SSP::data_output($columns, $data)
                        ]
        );
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preEdit($id) {

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
    protected function preSave($id, $data) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        if (!array_key_exists("car", $data) || !in_array($data["car"], $user_cars)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preDelete($id) {
        if (!is_null($id)) {
            $entry = $this->mapper->get($id);
            $user = $this->ci->get('helper')->getUser()->id;
            $user_cars = $this->car_mapper->getElementsOfUser($user);
            if (!in_array($entry->car, $user_cars)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

}
