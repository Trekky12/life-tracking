<?php

namespace App\Trips;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class TripService extends \App\Base\Service {

    protected $dataobject = \App\Trips\Trip::class;
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
