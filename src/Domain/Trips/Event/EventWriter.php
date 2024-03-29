<?php

namespace App\Domain\Trips\Event;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Trips\TripService;
use App\Domain\Trips\TripMapper;

class EventWriter extends ObjectActivityWriter {

    private $service;
    private $trip_service;
    private $trip_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, TripEventService $service, EventMapper $mapper, TripService $trip_service, TripMapper $trip_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->trip_service = $trip_service;
        $this->trip_mapper = $trip_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $trip = $this->trip_service->getFromHash($additionalData["trip"]);

        if (!$this->trip_service->isMember($trip->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($trip->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['trip'] = $trip->id;

        return parent::save($id, $data, $additionalData);
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
