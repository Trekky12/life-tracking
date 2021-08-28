<?php

namespace App\Domain\Car;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CarWriter extends ObjectActivityWriter {

    private $car_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CarMapper $mapper, CarService $car_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->car_service = $car_service;
    }

    public function save($id, $data, $additionalData = null): Payload {
        if ($this->car_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::save($id, $data, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'cars_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "cars";
    }

}
