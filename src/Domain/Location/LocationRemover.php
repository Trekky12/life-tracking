<?php

namespace App\Domain\Location;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Activity\ActivityCreator;
use App\Application\Payload\Payload;

class LocationRemover extends ObjectActivityRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity_creator, LocationMapper $mapper) {
        parent::__construct($logger, $user, $activity_creator);
        $this->mapper = $mapper;
    }

    public function delete($id, $user = null): Payload {
        return parent::delete($id, null);
    }

    public function getObjectViewRoute(): string {
        return 'location_edit';
    }

    public function getObjectViewRouteParams(int $id): array {
        return ["id" => $id];
    }

    public function getModule(): string {
        return "location";
    }

}
