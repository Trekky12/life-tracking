<?php

namespace App\Domain\Car;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class CarService extends GeneralService {

    public function __construct(LoggerInterface $logger, CurrentUser $user, CarMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function getUserCars() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getAllCarsOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function index() {
        $cars = $this->mapper->getAll('name');
        return new Payload(Payload::$RESULT_HTML, ["cars" => $cars]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

}
