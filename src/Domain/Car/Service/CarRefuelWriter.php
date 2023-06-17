<?php

namespace App\Domain\Car\Service;

use App\Domain\Car\Service\CarServiceWriter;

class CarRefuelWriter extends CarServiceWriter {

    public function getObjectViewRoute(): string {
        return 'car_service_refuel_edit';
    }
}
