<?php

namespace App\Domain\Location;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Finances\FinancesService;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceService;
use App\Application\Payload\Payload;

class LocationService extends Service {

    private $helper;
    private $finances_service;
    private $car_service;
    private $car_service_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        Helper $helper,
        LocationMapper $mapper,
        FinancesService $finances_service,
        CarService $car_service,
        CarServiceService $car_service_service
    ) {
        parent::__construct($logger, $user);
        $this->helper = $helper;
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->car_service = $car_service;
        $this->car_service_service = $car_service_service;
    }

    public function index($from, $to, $hide) {
        // Filtered markers

        list($hide_clusters) = $this->getHidden($hide);

        return new Payload(Payload::$RESULT_HTML, [
            "from" => $from,
            "to" => $to,
            "hide" => [
                "clusters" => $hide_clusters
            ]
        ]);
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

    public function getMarkers($from, $to) {
        $locations = $this->mapper->getMarkers($from, $to);
        $location_markers = array_map(function ($loc) {
            return $loc->getPosition();
        }, $locations);

        $finance_locations = $this->finances_service->getMarkers($from, $to);
        $finance_markers = array_map(function ($loc) {
            return $loc->getPosition();
        }, $finance_locations);

        $user_cars = $this->car_service->getUserCars();


        $carservice_locations = $this->car_service_service->getMarkers($from, $to, $user_cars);
        $carservice_markers = array_map(function ($loc) {
            return $loc->getPosition();
        }, $carservice_locations);

        $response_data = array_merge($location_markers, $finance_markers, $carservice_markers);

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getLastLocationTime() {
        if ($this->current_user->getUser()->hasModule('location')) {
            $last_time = $this->mapper->getLastTime();
            $date = new \DateTime($last_time);

            return new Payload(Payload::$RESULT_JSON, ["status" => "success", "ts" => $date->getTimestamp()]);
        }
        return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
    }

    public function getAddress($data) {

        $lat = array_key_exists('lat', $data) && !empty($data['lat']) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $lng = array_key_exists('lng', $data) && !empty($data['lng']) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $response_data = ['status' => 'error', 'data' => []];

        if (!is_null($lat) && !is_null($lng)) {

            $query = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $lat . '&lon=' . $lng;

            list($status, $result) = $this->helper->request($query);


            if ($status == 200) {
                $response_data['status'] = 'success';
                $array = json_decode($result, true);
                if (is_array($array) && array_key_exists("address", $array)) {
                    $response_data['data'] = $array["address"];
                }
            }
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }
}
