<?php

namespace App\Fuel;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $car_mapper;

    public function init() {
        $this->model = '\App\Fuel\FuelEntry';
        $this->index_route = 'fuel';

        $this->mapper = new \App\Fuel\Mapper($this->ci);
        $this->car_mapper = new \App\Car\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $list = $this->mapper->getAllofCars('date DESC', 10, $user_cars);
        $datacount = $this->mapper->countwithCars($user_cars);
        $cars = $this->car_mapper->getAll();

        return $this->ci->view->render($response, 'fuel/index.twig', ['list' => $list, 'datacount' => $datacount, 'cars' => $cars]);
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

        return $this->ci->view->render($response, 'fuel/edit.twig', ['entry' => $entry, 'cars' => $cars, 'user_cars' => $user_cars]);
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
        if ($entry->mileage && $entry->calc_consumption && $entry->type == 1 && $lastMileage) {

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
            if (!empty($el->consumption) && !empty($el->date)) {
                $raw_data[] = array("label" => $el->date, "car" => $el->car, "consumption" => $el->consumption);
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

        $table = [];

        foreach ($minMileages as $car => $minDate) {
            // is allowed?
            if (in_array($car, $user_cars)) {

                if (!array_key_exists($car, $table)) {
                    $table[$car] = array();
                }

                do {
                    $miledata = $this->mapper->sumMileageInterval($car, $minDate);
                    $minDate = $miledata["end"];
                    if ($miledata["diff"] > 0) {
                        $table[$car][] = $miledata;
                    }
                } while ($miledata["diff"] > 0);
            }
        }
        return $this->ci->view->render($response, 'fuel/stats.twig', ['data' => $data, "labels" => json_encode($labels), "table" => $table, "cars" => $cars]);
    }

    public function table(Request $request, Response $response) {


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
            array('db' => 'price', 'dt' => 3),
            array('db' => 'volume', 'dt' => 4),
            array('db' => 'total_price', 'dt' => 5),
            array(
                'db' => 'type',
                'dt' => 6,
                'formatter' => function( $d, $row ) {
                    if ($row["volume"] <= 0) {
                        return '';
                    }
                    return $d == 0 ? $this->ci->get('helper')->getTranslatedString("FUEL_PARTLY") : $this->ci->get('helper')->getTranslatedString("FUEL_FULL");
                }
            ),
            array('db' => 'consumption', 'dt' => 7),
            array('db' => 'location', 'dt' => 8),
            //array('db' => 'notice', 'dt' => 9),
            array(
                'db' => 'id',
                'dt' => 9,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('fuel_edit', ['id' => $d]);
                    return '<a href="' . $link . '"><span class="fa fa-pencil-square-o fa-lg"></span></a>';
                }
            ),
            array(
                'db' => 'id',
                'dt' => 10,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('fuel_delete', ['id' => $d]);
                    return '<a href="#" data-url="' . $link . '" class="btn-delete"><span class="fa fa-trash fa-lg"></span></a>';
                }
            )
        );


        $user = $this->ci->get('helper')->getUser()->id;
        //$whereUser = $user ? "(user = {$user} OR user IS NULL)" : "";

        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $whereCar = !empty($user_cars) ? "(car IN (" . implode(',', $user_cars) . "))" : " 1!=1";


        /**
         * @see https://github.com/DataTables/DataTablesSrc/blob/master/examples/server_side/scripts/ssp.class.php
         */
        $pdo = $this->ci->get('db');
        $data = \App\Main\SSP::complex($requestData, $pdo, "fuel", "id", $columns, null, $whereCar);
        return $response->withJson($data);
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
