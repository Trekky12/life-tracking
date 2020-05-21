<?php

namespace App\Domain\Car\Service;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Car\CarService;
use App\Application\Payload\Payload;

class CarServiceService extends Service {

    private $car_service;
    private $router;
    private $translation;
    
    public function __construct(LoggerInterface $logger, CurrentUser $user, CarServiceMapper $mapper, CarService $car_service, RouteParser $router, Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->car_service = $car_service;
        $this->router = $router;
        $this->translation = $translation;
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

        return new Payload(Payload::$RESULT_HTML, [
            'fuel_table' => $fuel_table,
            'datacount' => $fuel_datacount,
            'cars' => $cars,
            'service_table' => $service_table,
            'datacount2' => $service_datacount
        ]);
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
        return new Payload(Payload::$RESULT_JSON, $response_data);
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
        return new Payload(Payload::$RESULT_JSON, $response_data);
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

        return new Payload(Payload::$STATUS_UPDATE, null);
    }

    public function getMarkers($from, $to, $user_cars) {
        return $this->mapper->getMarkers($from, $to, $user_cars);
    }

    public function edit($entry_id, $type) {
        if (!is_null($entry_id) && !$this->hasAccessToCarOfEntry($entry_id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->getEntry($entry_id);

        $user_cars = $this->car_service->getUserCars();
        $cars = $this->car_service->getAllCarsOrderedByName();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'cars' => $cars, 'user_cars' => $user_cars, 'type' => $type]);
    }

}
