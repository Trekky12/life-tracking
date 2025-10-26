<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceStatsService;
use App\Domain\Car\Service\CarServiceMapper;
use Slim\Routing\RouteParser;

class CarMaxMileageTodayWidget implements Widget {

    private $translation;
    private $router;
    private $car_service;
    private $service;
    private $carservice_mapper;
    private $cars = [];

    public function __construct(Translator $translation, RouteParser $router, CarService $car_service, CarServiceStatsService $service, CarServiceMapper $carservice_mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->car_service = $car_service;
        $this->service = $service;
        $this->carservice_mapper = $carservice_mapper;

        $this->cars = $this->createList();
    }

    private function createList() {
        $cars = $this->car_service->getAllOrderedByName();

        $result = [];
        foreach ($cars as $car) {
            $result[$car->id] = ["name" => $car->name, "hash" => $car->getHash()];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->cars);
    }

    public function getContent(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        $totalMileagesWithStartDate = $this->carservice_mapper->getTotalMileage($id, true);

        $car = $this->car_service->getCar($id);
        $mileage = $this->service->getAllowedMileage($car, $totalMileagesWithStartDate["diff"]);
        if ($mileage) {
            return sprintf("%s %s", $mileage["remaining"], $this->translation->getTranslatedString("KM"));
        }
        return "";
    }

    public function getTitle(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        return sprintf("%s | %s", $this->translation->getTranslatedString("REMAINING_KM"), $this->cars[$id]["name"]);
    }

    public function getOptions(?WidgetObject $widget = null) {
        return [
            [
                "label" => $this->translation->getTranslatedString("CAR"),
                "data" => $this->createList(),
                "value" => !is_null($widget) ? $widget->getOptions()["car"] : null,
                "name" => "car",
                "type" => "select"
            ]
        ];
    }

    public function getLink(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        $hash = $this->cars[$id]["hash"];
        return $this->router->urlFor('car_service_stats', ['car' => $hash ]);
    }
}
