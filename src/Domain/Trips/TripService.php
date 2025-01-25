<?php

namespace App\Domain\Trips;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Trips\Event\EventMapper;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class TripService extends Service {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, TripMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function index($filter = null) {
        $trips = $this->mapper->getTripsOfUser($filter);

        return new Payload(Payload::$RESULT_HTML, ['trips' => $trips, "filter" => $filter]);
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
