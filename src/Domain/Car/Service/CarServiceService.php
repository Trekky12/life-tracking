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
use App\Domain\Main\Utility\Utility;

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

    public function indexRefuel($hash, $count = 20) {

        $car = $this->car_service->getFromHash($hash);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $fuel_list = $this->getMapper()->getTableDataFuel($car->id, 'date', 'DESC', $count);
        $fuel_table = $this->renderFuelTableRows($car, $fuel_list);
        $fuel_datacount = $this->getMapper()->tableCount($car->id, 0);

        return new Payload(Payload::$RESULT_HTML, [
            'fuel_table' => $fuel_table,
            'datacount' => $fuel_datacount,
            'car' => $car,
            'hasCarTable' => true
        ]);
    }

    public function indexService($hash, $count = 20) {

        $car = $this->car_service->getFromHash($hash);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $service_list = $this->getMapper()->getTableDataService($car->id, 'date', 'DESC', $count);
        $service_table = $this->renderServiceTableRows($car, $service_list);
        $service_datacount = $this->getMapper()->tableCount($car->id, 1);

        return new Payload(Payload::$RESULT_HTML, [
            'car' => $car,
            'service_table' => $service_table,
            'datacount2' => $service_datacount,
            'hasCarTable' => true
        ]);
    }

    private function renderFuelTableRows($car, array $table) {
        foreach ($table as &$row) {
            $row[8] = '<a href="' . $this->router->urlFor('car_service_refuel_edit', ['car' => $car->getHash(), 'id' => $row[8]]) . '">' . Utility::getFontAwesomeIcon('fas fa-pen-to-square') . '</a>';
            $row[9] = '<a href="#" data-url="' . $this->router->urlFor('car_service_refuel_delete', ['car' => $car->getHash(), 'id' => $row[9]]) . '" class="btn-delete">' . Utility::getFontAwesomeIcon('fas fa-trash') . '</a>';
        }
        return $table;
    }

    private function renderServiceTableRows($car, array $table) {
        foreach ($table as &$row) {
            $row[7] = '<a href="' . $this->router->urlFor('car_service_edit', ['car' => $car->getHash(), 'id' => $row[7]]) . '">' . Utility::getFontAwesomeIcon('fas fa-pen-to-square') . '</a>';
            $row[8] = '<a href="#" data-url="' . $this->router->urlFor('car_service_delete', ['car' => $car->getHash(), 'id' => $row[8]]) . '" class="btn-delete">' . Utility::getFontAwesomeIcon('fas fa-trash') . '</a>';
        }
        return $table;
    }

    public function fuelTable($hash, $requestData) {

        $car = $this->car_service->getFromHash($hash);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? Utility::filter_string_polyfill($requestData["searchQuery"]) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? Utility::filter_string_polyfill($requestData["sortDirection"]) : null;

        $recordsTotal = $this->getMapper()->tableCount($car->id, 0);
        $recordsFiltered = $this->getMapper()->tableCount($car->id, 0, $searchQuery);

        $lang = [0 => $this->translation->getTranslatedString("FUEL_PARTLY"), 1 => $this->translation->getTranslatedString("FUEL_FULL")];

        $data = $this->getMapper()->getTableDataFuel($car->id, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderFuelTableRows($car, $data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $table
        ];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function serviceTable($hash, $requestData) {

        $car = $this->car_service->getFromHash($hash);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? Utility::filter_string_polyfill($requestData["searchQuery"]) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? Utility::filter_string_polyfill($requestData["sortDirection"]) : null;

        $recordsTotal = $this->getMapper()->tableCount($car->id, 1);
        $recordsFiltered = $this->getMapper()->tableCount($car->id, 1, $searchQuery);

        $data = $this->getMapper()->getTableDataService($car->id, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderServiceTableRows($car, $data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $table
        ];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function hasAccessToCarOfEntry($id) {
        $entry = $this->mapper->get($id);
        $user_cars = $this->car_service->getUserElements();
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

    public function getMarkers($user_cars, $from, $to, $minLat, $maxLat, $minLng, $maxLng) {
        return $this->mapper->getMarkers($user_cars, $from, $to, $minLat, $maxLat, $minLng, $maxLng);
    }

    public function edit($hash, $entry_id) {

        $car = $this->car_service->getFromHash($hash);

        if (!$this->car_service->isMember($car->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($car->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'car' => $car
        ]);
    }
}
