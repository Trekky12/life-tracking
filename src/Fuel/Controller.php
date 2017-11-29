<?php

namespace App\Fuel;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Fuel\FuelEntry';
        $this->index_route = 'fuel';
        $this->edit_template = 'fuel/edit.twig';

        $this->mapper = new \App\Fuel\Mapper($this->ci, 'fuel', $this->model);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll('date DESC', 10);
        $datacount = $this->mapper->count();
        return $this->ci->view->render($response, 'fuel/index.twig', ['list' => $list, 'datacount' => $datacount]);
    }

    protected function afterSave($id) {
        
        $entry = $this->mapper->get($id);

        
        /**
         * Set Distance
         */
        if ($entry->mileage) {
            $lastMileage = $this->mapper->getLastMileage($id, $entry->mileage);
            if ($lastMileage) {
                $this->mapper->setDistance($id, $lastMileage);
            }
        }
        
        /**
         * Reset if set
         */
        if(!$entry->calc_consumption){
            $this->mapper->setConsumption($id, null);
        }

        /**
         * Calculate Consumption
         */
        if ($entry->mileage && $entry->calc_consumption && $lastMileage) {
            
            $lastFull = $this->mapper->getLastFull($id, $entry->mileage);
            if ($lastFull) {
                
                $distance = $entry->mileage - $lastFull->mileage;
                $volume = $this->mapper->getVolume($entry->mileage, $lastFull->mileage);
                $consumption = ($volume / $distance) * 100;
                
                $this->mapper->setConsumption($id, $consumption);
            }
        }
    }

    public function stats(Request $request, Response $response) {
        $list = $this->mapper->getAll('date ASC');

        $data = [];
        $dates = [];
        foreach ($list as $el) {
            if (!empty($el->consumption) && !empty($el->date)) {
                $dates[] = $el->date;
                $data[] = $el->consumption;
            }
        }

        return $this->ci->view->render($response, 'fuel/stats.twig', ['data' => json_encode($data, JSON_NUMERIC_CHECK), "labels" => json_encode($dates)]);
    }

    public function table(Request $request, Response $response) {


        $requestData = $request->getQueryParams();

        $columns = array(
            array('db' => 'date', 'dt' => 0),
            array('db' => 'mileage', 'dt' => 1),
            array('db' => 'price', 'dt' => 2),
            array('db' => 'volume', 'dt' => 3),
            array('db' => 'total_price', 'dt' => 4),
            array(
                'db' => 'type',
                'dt' => 5,
                'formatter' => function( $d, $row ) {
                    return $d == 0 ? $this->ci->get('helper')->getTranslatedString("FUEL_PARTLY") : $this->ci->get('helper')->getTranslatedString("FUEL_FULL");
                }
            ),
            array('db' => 'consumption', 'dt' => 6),
            array('db' => 'location', 'dt' => 7),
            array('db' => 'notice', 'dt' => 8),
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


        $user = $this->ci->get('helper')->getUser();
        $whereUser = $user ? "(user = {$user->id} OR user IS NULL)" : "";

        /**
         * @see https://github.com/DataTables/DataTablesSrc/blob/master/examples/server_side/scripts/ssp.class.php
         */
        $pdo = $this->ci->get('db');
        $data = \App\Main\SSP::complex($requestData, $pdo, "fuel", "id", $columns, null, $whereUser);
        return $response->withJson($data);
    }

}
