<?php

namespace App\Domain\Trips\Event;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Trips\TripService;
use App\Domain\Main\Helper;
use App\Application\Payload\Payload;

class TripEventService extends GeneralService {

    private $trip_service;
    private $settings;
    private $translation;
    private $helper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, EventMapper $mapper, TripService $trip_service, Settings $settings, Translator $translation, Helper $helper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->trip_service = $trip_service;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->helper = $helper;
    }

    public function view($hash, $from, $to): Payload {

        $trip = $this->trip_service->getFromHash($hash);

        if (!$this->trip_service->isMember($trip->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = $this->getTripEvents($trip, $from, $to);

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function getTripEvents($trip, $from, $to) {

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

    public function getMarkers($hash, $from, $to) {

        $trip = $this->trip_service->getFromHash($hash);

        if (!$this->trip_service->isMember($trip->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

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

        return new Payload(Payload::$RESULT_JSON, $markers);
    }

    public function getLatLng($data) {

        $address = array_key_exists('address', $data) && !empty($data['address']) ? filter_var($data['address'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $response_data = ['status' => 'error', 'data' => []];

        if (!is_null($address)) {

            $result = $this->getLocation($address);
            if ($result !== false) {
                $response_data['status'] = 'success';
                $response_data['data'] = $result;
            }
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    private function getLocation($address) {
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

    public function edit($hash, $entry_id, $from, $to) {

        $trip = $this->trip_service->getFromHash($hash);

        if (!$this->trip_service->isMember($trip->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        $response_data = [
            'entry' => $entry,
            'trip' => $trip,
            'types' => $this->getEventTypes(),
            'from' => $from,
            'to' => $to,
            "isTripEventEdit" => true
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function updatePosition($data) {

        if (array_key_exists("events", $data) && !empty($data["events"])) {
            $events = filter_var_array($data["events"], FILTER_SANITIZE_NUMBER_INT);
            $user = $this->current_user->getUser()->id;

            foreach ($events as $position => $item) {
                $this->mapper->updatePosition($item, $position, $user);
            }


            $response_data = ['status' => 'success'];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
        $response_data = ['status' => 'error'];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
