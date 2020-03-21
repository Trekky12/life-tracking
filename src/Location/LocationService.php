<?php

namespace App\Location;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\Main\Helper;
use App\Finances\FinancesService;
use App\Car\CarService;
use App\Car\Service\CarServiceService;

class LocationService extends \App\Base\Service {

    protected $dataobject = \App\Location\Location::class;
    protected $element_view_route = 'location_edit';
    protected $create_activity = false;
    protected $module = "location";
    private $helper;
    private $finances_service;
    private $car_service;
    private $car_service_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Helper $helper,
            Mapper $mapper,
            FinancesService $finances_service,
            CarService $car_service,
            CarServiceService $car_service_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->helper = $helper;
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->car_service = $car_service;
        $this->car_service_service = $car_service_service;
    }

    public function index($from, $to, $hide) {
        // Filtered markers

        list($hide_clusters) = $this->getHidden($hide);

        return [
            "from" => $from,
            "to" => $to,
            "hide" => [
                "clusters" => $hide_clusters
            ]
        ];
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
        $location_markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $locations);

        $finance_locations = $this->finances_service->getMarkers($from, $to);
        $finance_markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $finance_locations);

        $user_cars = $this->car_service->getUserCars();


        $carservice_locations = $this->car_service_service->getMarkers($from, $to, $user_cars);
        $carservice_markers = array_map(function($loc) {
            return $loc->getPosition();
        }, $carservice_locations);

        $response_data = array_merge($location_markers, $finance_markers, $carservice_markers);

        return $response_data;
    }

    public function getAddress($lat, $lng) {
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
        return $response_data;
    }

}
