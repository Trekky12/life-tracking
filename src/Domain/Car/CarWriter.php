<?php

namespace App\Domain\Car;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CarWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CarMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $user = null): Payload {
        return parent::save($id, $data, $user);
    }

    public function getObjectViewRoute(): string {
        return 'cars_edit';
    }

    public function getObjectViewRouteParams(int $id): array {
        return ["id" => $id];
    }

    public function getModule(): string {
        return "cars";
    }

}
