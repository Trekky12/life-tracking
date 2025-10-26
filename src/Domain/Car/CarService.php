<?php

namespace App\Domain\Car;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class CarService extends Service {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CarMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function getUserCars() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getAllOrderedByName($archive = null) {
        return $this->mapper->getUserItems('t.createdOn DESC, name', false, null, $archive);
    }

    public function getCar($id) {
        return $this->mapper->get($id);
    }

    public function index($archive = 0) {
        $cars = $this->getAllOrderedByName($archive);

        return new Payload(Payload::$RESULT_HTML, [
            "cars" => $cars,
            'archive' => $archive
        ]);
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
