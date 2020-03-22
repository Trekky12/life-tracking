<?php

namespace App\Domain\Car\Service;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Car\CarService;

class CarServiceService extends \App\Domain\Service {

    protected $module = "cars";
    protected $dataobject = \App\Domain\Car\Service\CarServiceEntry::class;
    protected $dataobject_parent = \App\Domain\Car\Car::class;
    protected $element_view_route = 'car_service_edit';
    private $car_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            CarService $car_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->car_service = $car_service;
    }

    public function index() {
        $user_cars = $this->car_service->getUserCars();

        $fuel_list = $this->mapper->tableDataFuel($user_cars, 'date', 'DESC', 10);
        $fuel_table = $this->renderFuelTableRows($fuel_list);
        $fuel_datacount = $this->mapper->tableCount($user_cars, 0);

        $service_list = $this->mapper->tableDataService($user_cars, 'date', 'DESC', 10);
        $service_table = $this->renderServiceTableRows($service_list);
        $service_datacount = $this->mapper->tableCount($user_cars, 1);

        $cars = $this->car_service->getAllCarsOrderedByName();

        return [
            'fuel_table' => $fuel_table,
            'datacount' => $fuel_datacount,
            'cars' => $cars,
            'service_table' => $service_table,
            'datacount2' => $service_datacount
        ];
    }

    private function renderFuelTableRows(array $table) {
        foreach ($table as &$row) {
            $row[9] = '<a href="' . $this->router->urlFor('car_service_edit', ['id' => $row[9]]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[10] = '<a href="#" data-url="' . $this->router->urlFor('car_service_delete', ['id' => $row[10]]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>';
        }
        return $table;
    }

    private function renderServiceTableRows(array $table) {
        foreach ($table as &$row) {
            $row[8] = '<a href="' . $this->router->urlFor('car_service_edit', ['id' => $row[8]]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[9] = '<a href="#" data-url="' . $this->router->urlFor('car_service_delete', ['id' => $row[9]]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>';
        }
        return $table;
    }

    public function calculateFuelConsumption($id) {
        $entry = $this->mapper->get($id);


        /**
         * Set Distance
         */
        if ($entry->mileage) {
            $lastMileage = $this->mapper->getLastMileage($id, $entry->mileage, $entry->car);
            if (!is_null($lastMileage)) {
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
        if ($entry->mileage && $entry->fuel_calc_consumption && $entry->fuel_type == 1 && !is_null($lastMileage)) {

            $lastFull = $this->mapper->getLastFull($id, $entry->mileage, $entry->car);
            if ($lastFull) {

                $distance = $entry->mileage - $lastFull->mileage;
                $volume = $this->mapper->getVolume($entry->car, $entry->date, $lastFull->date);
                $consumption = ($volume / $distance) * 100;

                $this->mapper->setConsumption($id, $consumption);
            }
        }
    }

    public function fuelTable($requestData) {
        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $user_cars = $this->car_service->getUserCars();

        $recordsTotal = $this->mapper->countwithCars($user_cars);
        $recordsFiltered = $this->mapper->tableCount($user_cars, 0, $searchQuery);

        $lang = [0 => $this->translation->getTranslatedString("FUEL_PARTLY"), 1 => $this->translation->getTranslatedString("FUEL_FULL")];

        $data = $this->mapper->tableDataFuel($user_cars, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderFuelTableRows($data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $table
        ];
        return $response_data;
    }

    public function serviceTable($requestData) {
        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $user_cars = $this->car_service->getUserCars();

        $recordsTotal = $this->mapper->countwithCars($user_cars, 1);
        $recordsFiltered = $this->mapper->tableCount($user_cars, 1, $searchQuery);

        $data = $this->mapper->tableDataService($user_cars, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderServiceTableRows($data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $table
        ];
        return $response_data;
    }

    protected function getParentObjectService() {
        return $this->car_service;
    }

    public function hasAccessToCarOfEntry($id) {
        $entry = $this->mapper->get($id);
        $user_cars = $this->car_service->getUserCars();
        if (!in_array($entry->car, $user_cars)) {
            return false;
        }
        return true;
    }

    public function setCalculationType($data) {
        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1, 2))) {
            SessionUtility::setSessionVar('mileage_type', $data["state"]);
        }
    }

    public function getMarkers($from, $to) {
        return $this->mapper->getMarkers($from, $to);
    }

}
