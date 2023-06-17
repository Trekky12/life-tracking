<?php

namespace App\Domain\Car\Service;

class CarRefuelRemover extends CarServiceRemover {

    public function getObjectViewRoute(): string {
        return 'car_service_refuel_edit';
    }
}
