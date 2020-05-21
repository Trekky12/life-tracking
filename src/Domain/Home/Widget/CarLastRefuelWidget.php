<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceStatsService;
use App\Domain\Car\Service\CarServiceMapper;

class CarLastRefuelWidget {

    private $logger;
    private $current_user;
    private $car_service;
    private $service;
    private $carservice_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CarService $car_service, CarServiceStatsService $service, CarServiceMapper $carservice_mapper) {
        $this->logger = $logger;
        $this->current_user = $user;
        $this->car_service = $car_service;
        $this->service = $service;
        $this->carservice_mapper = $carservice_mapper;
    }

    public function getContent() {
        $user_cars = $this->car_service->getUserCars();

        $cars = $this->car_service->getAllCarsOrderedByName();

        $result = [];
        foreach ($user_cars as $car_id) {
            $list = $this->carservice_mapper->tableDataFuel([$car_id], "mileage", "DESC", 2);
            $result[$car_id] = ["name" => $cars[$car_id]->name, "list" => $list];
        }

        return $result;
    }

}
