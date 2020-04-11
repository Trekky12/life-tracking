<?php

namespace App\Domain\Trips;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Trips\Event\EventMapper;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class TripService extends GeneralService {
    
    private $event_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, TripMapper $mapper, EventMapper $event_mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->event_mapper = $event_mapper;
        $this->user_service = $user_service;
    }

    public function index() {
        $trips = $this->mapper->getUserItems('t.createdOn DESC, name');
        $dates = $this->event_mapper->getMinMaxEventsDates();

        return new Payload(Payload::$RESULT_HTML, ['trips' => $trips, 'dates' => $dates]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

}
