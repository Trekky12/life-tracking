<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceStatsService;
use App\Domain\Car\Service\CarServiceMapper;

class CarLastRefuelWidget implements Widget {

    private $logger;
    private $translation;
    private $current_user;
    private $car_service;
    private $service;
    private $carservice_mapper;
    private $cars = [];

    public function __construct(LoggerInterface $logger, Translator $translation, CurrentUser $user, CarService $car_service, CarServiceStatsService $service, CarServiceMapper $carservice_mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->current_user = $user;
        $this->car_service = $car_service;
        $this->service = $service;
        $this->carservice_mapper = $carservice_mapper;

        $this->cars = $this->createList();
    }

    private function createList() {
        $user_cars = $this->car_service->getUserCars();

        $cars = $this->car_service->getAllCarsOrderedByName();

        $result = [];
        foreach ($user_cars as $car_id) {
            $list = $this->carservice_mapper->tableDataFuel([$car_id], "mileage", "DESC", 2);
            $result[$car_id] = ["name" => $cars[$car_id]->name, "list" => $list];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->cars);
    }

    public function getContent(WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        return $this->cars[$id]["list"];
    }

    public function getTitle(WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        return sprintf("%s | %s", $this->translation->getTranslatedString("CAR_REFUEL"), $this->cars[$id]["name"]);
    }

    public function getOptions() {
        return [
            [
                "label" => $this->translation->getTranslatedString("CAR"),
                "data" => $this->createList(),
                "name" => "car",
                "type" => "select"
            ]
        ];
    }

}
