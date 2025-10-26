<?php

namespace App\Domain\Car\Service;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Car\CarService;
use App\Domain\Main\Translator;

class CarServiceWriter extends ObjectActivityWriter {

    protected $car_service;
    protected $translation;
    protected $service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        CarServiceMapper $mapper,
        CarService $car_service,
        Translator $translation,
        CarServiceService $service,
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->car_service = $car_service;
        $this->translation = $translation;
        $this->service = $service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $car = $this->car_service->getFromHash($additionalData["car"]);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($car->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['car'] = $car->id;

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->calculateFuelConsumption($entry->id);

        return $payload;
    }

    protected function calculateFuelConsumption($id) {
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


    public function getParentMapper() {
        return $this->car_service->getMapper();
    }

    public function getObjectViewRoute(): string {
        return 'car_service_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        $car = $this->getParentMapper()->get($entry->getParentID());
        return [
            "car" => $car->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "cars";
    }
}
