<?php

namespace App\Domain\Trips;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;

class TripService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Trips\Trip::class;
    protected $element_view_route = 'trips_edit';
    protected $module = "trips";

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

    public function getUserTrips() {
        return $this->mapper->getUserItems('t.createdOn DESC, name');
    }

}
