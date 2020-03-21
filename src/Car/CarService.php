<?php

namespace App\Car;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class CarService extends \App\Base\Service {

    protected $dataobject = \App\Car\Car::class;
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
