<?php

namespace App\Domain\Trips\Route;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Trips\TripService;
use App\Domain\Trips\TripMapper;

class RouteRemover extends ObjectActivityRemover {

    private $trip_service;
    private $trip_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, RouteMapper $mapper, TripService $trip_service, TripMapper $trip_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->trip_service = $trip_service;
        $this->trip_mapper = $trip_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $group = $this->trip_service->getFromHash($additionalData["trip"]);

        if (!$this->trip_service->isMember($group->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->trip_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'trips_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $trip = $this->getParentMapper()->get($entry->getParentID());
        return [
            "trip" => $trip->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "trips";
    }

}
