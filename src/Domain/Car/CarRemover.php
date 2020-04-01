<?php

namespace App\Domain\Car;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CarRemover extends ObjectActivityRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CarMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function delete($id, $user = null): Payload {
        return parent::delete($id, null);
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
