<?php

namespace App\Trips\Event;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\Trips\TripService;
use App\Main\Helper;

class TripEventService extends \App\Base\Service {

    protected $dataobject = \App\Trips\Event\Event::class;
    protected $dataobject_parent = \App\Trips\Trip::class;
    protected $element_view_route = 'trips_view';
    protected $module = "trips";
    private $trip_service;
    private $helper;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            TripService $trip_service,
            Helper $helper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->trip_service = $trip_service;
        $this->helper = $helper;
    }

    public function getMinMaxEventsDates() {
        return $this->mapper->getMinMaxEventsDates();
    }

    public function getTripEvents($trip, $from, $to) {

        // always show all events (hide the one not in range)
        $events = $this->mapper->getFromTrip($trip->id, null, null, "start_date, start_time, end_date, end_time, position");

        list($min, $max) = $this->mapper->getMinMaxEventsDate($trip->id);

        $dateInterval = [];
        if (!is_null($min) && !is_null($max)) {
            // add last day
            $dateMax = new \DateTime($max);
            $dateMax->add(new \DateInterval('P1D'));

            $dateInterval = new \DatePeriod(
                    new \DateTime($min),
                    new \DateInterval('P1D'),
                    $dateMax
            );
        }

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt2 = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP["trips_buttons"]);
        $fmt2->setPattern($dateFormatPHP["trips_list"]);

        $dateRange = [];
        $dateRange['all'] = ["date" => null, "display_date" => $this->translation->getTranslatedString("TRIPS_OVERVIEW"), "events" => []];
        foreach ($dateInterval as $d) {
            $date = $d->format('Y-m-d');
            $dateRange[$date] = ["date" => $date, "display_date" => $fmt->format($d), "full_date" => $fmt2->format($d), "events" => [], "active" => false];
        }

        if (!empty($from)) {
            $dateRange[$from]["active"] = true;
        } elseif (array_key_exists(date('Y-m-d'), $dateRange)) {
            $dateRange[date('Y-m-d')]["active"] = true;
        } else {
            $dateRange["all"]["active"] = true;
        }


        $dateFormatter = new \IntlDateFormatter($language, NULL, NULL);
        $timeFormatter = new \IntlDateFormatter($language, NULL, NULL);
        $datetimeFormatter = new \IntlDateFormatter($language, NULL, NULL);
        $dateFormatter->setPattern($dateFormatPHP['date']);
        $timeFormatter->setPattern($dateFormatPHP['time']);
        $datetimeFormatter->setPattern($dateFormatPHP['datetime']);

        $fromTranslation = $this->translation->getTranslatedString("FROM");
        $toTranslation = $this->translation->getTranslatedString("TO");

        foreach ($events as $ev) {

            if (empty($ev->start_date) && empty($ev->end_date)) {
                $dateRange["all"]["events"][] = $ev;
                continue;
            }

            $end_date = !empty($ev->end_date) ? $ev->end_date : $ev->start_date;
            $end = new \DateTime($end_date);
            $end->add(new \DateInterval('P1D'));

            $interval = new \DatePeriod(
                    new \DateTime($ev->start_date),
                    new \DateInterval('P1D'),
                    $end
            );

            foreach ($interval as $event_date) {
                $datekey = $event_date->format('Y-m-d');

                $dateRange[$datekey]["events"][] = $ev;
            }

            // create Popup
            $ev->createPopup($dateFormatter, $timeFormatter, $datetimeFormatter, $fromTranslation, $toTranslation, ', ', '');
        }

        $mapbox_token = $this->settings->getAppSettings()['mapbox_token'];

        return [
            "events" => $events,
            "trip" => $trip,
            "isTrips" => true,
            "from" => $from,
            "to" => $to,
            "range" => $dateRange,
            "mapbox_token" => $mapbox_token
        ];
    }

    public function getMarkers($trip, $from, $to) {
        $events = $this->mapper->getFromTrip($trip->id, $from, $to, "start_date, start_time, end_date, end_time, position");

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $dateFormatter = new \IntlDateFormatter($language, NULL, NULL);
        $timeFormatter = new \IntlDateFormatter($language, NULL, NULL);
        $datetimeFormatter = new \IntlDateFormatter($language, NULL, NULL);
        $dateFormatter->setPattern($dateFormatPHP['date']);
        $timeFormatter->setPattern($dateFormatPHP['time']);
        $datetimeFormatter->setPattern($dateFormatPHP['datetime']);

        $fromTranslation = $this->translation->getTranslatedString("FROM");
        $toTranslation = $this->translation->getTranslatedString("TO");

        $markers = [];
        foreach ($events as $ev) {
            // create Popup
            $ev->createPopup($dateFormatter, $timeFormatter, $datetimeFormatter, $fromTranslation, $toTranslation, '<br/>', '<br/>');
            $markers[] = $ev->getPosition();
        }
        return $markers;
    }

    public function getLocation($address) {
        $query = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);

        list($status, $result) = $this->helper->request($query);

        if ($status == 200) {
            $result = json_decode($result, true);
            if (is_array($result)) {
                return $result;
            }
        }
        return false;
    }

    public function changePosition($events) {
        $user = $this->current_user->getUser()->id;

        foreach ($events as $position => $item) {
            $this->mapper->updatePosition($item, $position, $user);
        }
    }

    public static function getEventTypes() {
        return [
            "EVENT",
            "HOTEL",
            "FLIGHT",
            "DRIVE",
            "TRAINRIDE",
            "CARRENTAL",
        ];
    }

    protected function getElementViewRoute($entry) {
        $trip = $this->getParentObjectService()->getEntry($entry->getParentID());
        $this->element_view_route_params["trip"] = $trip->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectService() {
        return $this->trip_service;
    }

}
