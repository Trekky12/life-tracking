<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceStatsService;
use App\Domain\Car\Service\CarServiceMapper;
use Slim\Routing\RouteParser;

class CarLastRefuelWidget implements Widget {

    private $translation;
    private $router;
    private $car_service;
    private $carservice_mapper;
    private $cars = [];

    public function __construct(Translator $translation, RouteParser $router, CarService $car_service, CarServiceMapper $carservice_mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->car_service = $car_service;
        $this->carservice_mapper = $carservice_mapper;

        $this->cars = $this->createList();
    }

    private function createList() {
        $user_cars = $this->car_service->getUserCars();

        $cars = $this->car_service->getAllCarsOrderedByName();

        $result = [];
        foreach ($user_cars as $car_id) {
            $result[$car_id] = ["name" => $cars[$car_id]->name];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->cars);
    }

    public function getContent(WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        return $this->carservice_mapper->getTableDataFuel([$id], "mileage", "DESC", 2);
    }

    public function getTitle(WidgetObject $widget = null) {
        $id = $widget->getOptions()["car"];
        return sprintf("%s | %s", $this->translation->getTranslatedString("CAR_REFUEL"), $this->cars[$id]["name"]);
    }

    public function getOptions(WidgetObject $widget = null) {
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

    public function getLink(WidgetObject $widget = null) {
        return $this->router->urlFor('car_service');
    }

}
