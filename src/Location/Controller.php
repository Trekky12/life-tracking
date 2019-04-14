<?php

namespace App\Location;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Location\Location';
        $this->index_route = 'location';

        $this->mapper = new \App\Location\Mapper($this->ci);
        $this->finance_mapper = new \App\Finances\Mapper($this->ci);
        $this->car_mapper = new \App\Car\Mapper($this->ci);
        $this->carservice_mapper = new \App\Car\Service\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $my_locations = $this->mapper->getAll();

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        // Filtered markers
        $hide = $request->getQueryParam('hide');
        list($hide_location, $hide_finances, $hide_cars) = $this->getHidden($hide);

        return $this->ci->view->render($response, 'location/index.twig', [
                    "tracks" => $my_locations,
                    "from" => $from,
                    "to" => $to,
                    "hide" => [
                        "location" => $hide_location,
                        "finances" => $hide_finances,
                        "cars" => $hide_cars
                    ]
        ]);
    }

    public function getMarkers(Request $request, Response $response) {

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        $hide = $request->getQueryParam('hide');
        list($hide_location, $hide_finances, $hide_cars) = $this->getHidden($hide);
        
        $location_markers = [];
        if (!$hide_location) {
            $locations = $this->mapper->getMarkers($from, $to);
            $location_markers = array_map(function($loc) {
                return $loc->getPosition();
            }, $locations);
        }

        $finance_markers = [];
        if (!$hide_finances) {
            $finance_locations = $this->finance_mapper->getMarkers($from, $to);
            $finance_markers = array_map(function($loc) {
                return $loc->getPosition();
            }, $finance_locations);
        }

        $carservice_markers = [];
        if (!$hide_cars) {
            $user = $this->ci->get('helper')->getUser()->id;
            $user_cars = $this->car_mapper->getElementsOfUser($user);
            $carservice_locations = $this->carservice_mapper->getMarkers($from, $to, $user_cars);
            $carservice_markers = array_map(function($loc) {
                return $loc->getPosition();
            }, $carservice_locations);
        }

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
        $hide_location = false;
        $hide_finances = false;
        $hide_cars = false;

        if (!is_null($hide)) {
            $hidden_markers = filter_var_array($hide, FILTER_SANITIZE_STRING);

            if (in_array("location", $hidden_markers)) {
                $hide_location = true;
            }
            if (in_array("finances", $hidden_markers)) {
                $hide_finances = true;
            }
            if (in_array("cars", $hidden_markers)) {
                $hide_cars = true;
            }
        }
        return array($hide_location, $hide_finances, $hide_cars);
    }

}
