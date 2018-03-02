<?php

namespace App\Location;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Location\Location';
        $this->index_route = 'location';
        
        $this->mapper = new \App\Location\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $my_locations = $this->mapper->getAll();

        $data = $request->getQueryParams();
        list($from, $to) = $this->getDateRange($data);

        return $this->ci->view->render($response, 'location/index.twig', ['tracks' => $my_locations, 'from' => $from, 'to' => $to]);
    }

    public function getMarkers(Request $request, Response $response) {

        $data = $request->getQueryParams();
        list($from, $to) = $this->getDateRange($data);


        $my_locations = $this->mapper->getMarkers($from, $to);

        $markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $my_locations);

        return $response->withJSON($markers);
    }


    private function getDateRange($data) {

        $from = array_key_exists('from', $data) && !empty($data['from']) ? filter_var($data['from'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $to = array_key_exists('to', $data) && !empty($data['to']) ? filter_var($data['to'], FILTER_SANITIZE_STRING) : date('Y-m-d');


        /**
         * Clean dates
         */
        $dateRegex = "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/";
        if (!preg_match($dateRegex, $from) || !preg_match($dateRegex, $to)) {

            $from = preg_match($dateRegex, $from) ? $from : date('Y-m-d');
            $to = preg_match($dateRegex, $to) ? $to : date('Y-m-d');
        }

        return array($from, $to);
    }

    public function getAddress(Request $request, Response $response) {

        $id = $request->getAttribute('id');
        $loc = $this->mapper->get($id);

        $query = 'http://nominatim.openstreetmap.org/reverse?format=json&lat=' . $loc->net_lat . '&lon=' . $loc->net_lng;

        list($status, $result) = $this->ci->get('helper')->request($query);

        $newResponse = ['status' => 'success', 'data' => []];

        if ($status == 200) {
            $array = json_decode($result, true);
            if (is_array($array) && array_key_exists("address", $array)) {
                $newResponse['data'] = $array["address"];
            }
        }

        return $response->withJson($newResponse);
    }

}
