<?php

namespace App\Domain\Car;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class CarService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Car\Car::class;
    protected $element_view_route = 'cars_edit';
    protected $module = "cars";

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getUserCars() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getAllCarsOrderedByName() {
        return $this->mapper->getAll('name');
    }

}
