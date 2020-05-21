<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceStatsService;
use App\Domain\Car\Service\CarServiceMapper;

class CarMaxMileageTodayWidget {

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
        $totalMileagesWithStartDate = $this->carservice_mapper->getTotalMileage(true);

        $result = [];

        foreach ($user_cars as $car_id) {
            $car = $cars[$car_id];
            $current_mileage_year = array_key_exists($car_id, $totalMileagesWithStartDate) ? $totalMileagesWithStartDate[$car_id]["diff"] : null;

            $mileage = $this->service->getAllowedMileage($car, $current_mileage_year);

            if (!is_null($mileage)) {
                $result[$car_id] = ["name" => $car->name, "remaining" => $mileage["remaining"]];
            }
        }

        return $result;
    }

}
