<?php

namespace App\Domain\Car\Service;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Car\CarService;

class CarServiceRemover extends ObjectActivityRemover {

    private $service;
    private $car_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        CarServiceMapper $mapper,
        CarServiceService $service,
        CarService $car_service
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->car_service = $car_service;
    }

    public function delete($id, $additionalData = null): Payload {

        $car = $this->car_service->getFromHash($additionalData["car"]);

        if (!$this->car_service->isMember($car->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($car->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return parent::delete($id, $additionalData);
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
