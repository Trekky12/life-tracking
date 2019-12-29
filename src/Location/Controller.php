<?php

namespace App\Location;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Location\Location';
    protected $index_route = 'location';
    protected $edit_template = 'location/edit.twig';
    protected $element_view_route = 'location_edit';
    protected $create_activity = false;
    protected $module = "location";
    
    private $finance_mapper;
    private $car_mapper;
    private $carservice_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->finance_mapper = new \App\Finances\Mapper($this->ci);
        $this->car_mapper = new \App\Car\Mapper($this->ci);
        $this->carservice_mapper = new \App\Car\Service\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        // Filtered markers
        $hide = $request->getQueryParam('hide');
        list($hide_clusters) = $this->getHidden($hide);

        return $this->ci->view->render($response, 'location/index.twig', [
                    "from" => $from,
                    "to" => $to,
                    "hide" => [
                        "clusters" => $hide_clusters
                    ]
        ]);
    }

    public function getMarkers(Request $request, Response $response) {

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        $locations = $this->mapper->getMarkers($from, $to);
        $location_markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $locations);

        $finance_locations = $this->finance_mapper->getMarkers($from, $to);
        $finance_markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $finance_locations);

        $user = $this->ci->get('helper')->getUser()->id;
        $user_cars = $this->car_mapper->getElementsOfUser($user);
        $carservice_locations = $this->carservice_mapper->getMarkers($from, $to, $user_cars);
        $carservice_markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $carservice_locations);

        return $response->withJSON(array_merge($location_markers, $finance_markers, $carservice_markers));
    }

    public function getAddress(Request $request, Response $response) {

        //$id = $request->getAttribute('id');
        //$loc = $this->mapper->get($id);

        $data = $request->getQueryParams();
        $lat = array_key_exists('lat', $data) && !empty($data['lat']) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $lng = array_key_exists('lng', $data) && !empty($data['lng']) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $newResponse = ['status' => 'error', 'data' => []];

        if (!is_null($lat) && !is_null($lng)) {

            $query = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $lat . '&lon=' . $lng;

            list($status, $result) = $this->ci->get('helper')->request($query);


            if ($status == 200) {
                $newResponse['status'] = 'success';
                $array = json_decode($result, true);
                if (is_array($array) && array_key_exists("address", $array)) {
                    $newResponse['data'] = $array["address"];
                }
            }
        }

        return $response->withJson($newResponse);
    }

    private function getHidden($hide) {
        $hide_clusters = false;

        if (!is_null($hide)) {
            $hidden_markers = filter_var_array($hide, FILTER_SANITIZE_STRING);

            if (in_array("clusters", $hidden_markers)) {
                $hide_clusters = true;
            }
        }
        return array($hide_clusters);
    }

    protected function preSave($id, array &$data, Request $request) {
        if (!array_key_exists("device", $data)) {
            $data["device"] = $this->ci->get('helper')->getAgent();
        }
    }

    public function steps(Request $request, Response $response) {
        $steps = $this->mapper->getStepsPerYear();
        list($chart_data, $labels) = $this->createChartData($steps);
        return $this->ci->view->render($response, 'location/steps/steps.twig', ['stats' => $steps, "data" => $chart_data, "labels" => $labels]);
    }

    public function stepsYear(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $steps = $this->mapper->getStepsOfYear($year);
        list($chart_data, $labels) = $this->createChartData($steps, "month");
        return $this->ci->view->render($response, 'location/steps/steps_year.twig', ['stats' => $steps, "year" => $year, "data" => $chart_data, "labels" => $labels]);
    }

    public function stepsMonth(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $steps = $this->mapper->getStepsOfYearMonth($year, $month);
        list($chart_data, $labels) = $this->createChartData($steps, "date");
        return $this->ci->view->render($response, 'location/steps/steps_month.twig', ['stats' => $steps, "year" => $year, "month" => $month, "data" => $chart_data, "labels" => $labels]);
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
                return $this->ci->get('helper')->getMonthName($l);
            }, $labels);
        }
        if ($key === "date") {
            $labels = array_map(function($l) {
                return $this->ci->get('helper')->getDay($l);
            }, $labels);
        }

        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);
        $labels = json_encode($labels, JSON_NUMERIC_CHECK);

        return array($data, $labels);
    }

}
