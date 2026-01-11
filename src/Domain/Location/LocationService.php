<?php

namespace App\Domain\Location;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\Finances\FinancesService;
use App\Domain\Car\CarService;
use App\Domain\Car\Service\CarServiceService;
use App\Application\Payload\Payload;
use App\Domain\Base\Settings;
use App\Domain\Main\Translator;
use App\Domain\Splitbill\Bill\SplitbillBillService;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\Trips\Event\TripEventService;
use App\Domain\Trips\TripService;

class LocationService extends Service {

    private $helper;
    private $translation;
    private $settings;
    private $finances_service;
    private $car_service;
    private $car_service_service;
    private $splitbill_group_service;
    private $splitbill_bill_service;
    private $timesheet_project_service;
    private $timesheet_sheet_service;
    private $trip_service;
    private $trip_event_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        Helper $helper,
        Translator $translation,
        Settings $settings,
        LocationMapper $mapper,
        FinancesService $finances_service,
        CarService $car_service,
        CarServiceService $car_service_service,
        SplitbillGroupService $splitbill_group_service,
        SplitbillBillService $splitbill_bill_service,
        ProjectService $timesheet_project_service,
        SheetService $timesheet_sheet_service,
        TripService $trip_service,
        TripEventService $trip_event_service
    ) {
        parent::__construct($logger, $user);
        $this->helper = $helper;
        $this->translation = $translation;
        $this->settings = $settings;
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->car_service = $car_service;
        $this->car_service_service = $car_service_service;
        $this->splitbill_group_service = $splitbill_group_service;
        $this->splitbill_bill_service = $splitbill_bill_service;
        $this->timesheet_project_service = $timesheet_project_service;
        $this->timesheet_sheet_service = $timesheet_sheet_service;
        $this->trip_service = $trip_service;
        $this->trip_event_service = $trip_event_service;
    }

    public function index($from, $to, $hide) {
        // Filtered markers

        list($hide_clusters) = $this->getHidden($hide);

        return new Payload(Payload::$RESULT_HTML, [
            "from" => $from,
            "to" => $to,
            "hide" => [
                "clusters" => $hide_clusters
            ]
        ]);
    }

    private function getHidden($hide) {
        $hide_clusters = false;

        if (!is_null($hide)) {
            $hidden_markers = filter_var_array($hide, FILTER_UNSAFE_RAW);

            if (in_array("clusters", $hidden_markers)) {
                $hide_clusters = true;
            }
        }
        return array($hide_clusters);
    }

    public function getMarkers($from, $to) {

        $count = [
            "location" => [],
            "finances" => [],
            "cars" => [],
            "splitbills" => [],
            "timesheets" => [],
            "trips" => []
        ];

        $locations = $this->mapper->getMarkers($from, $to);
        $location_markers = array_map(function ($loc) use (&$count) {

            $position = $loc->getPosition();

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
            $dateFormatter = new \IntlDateFormatter($language);
            $dateFormatter->setPattern($dateFormatPHP['date']);
            $date = $dateFormatter->format(new \DateTime($position["dt"]));
            if (!array_key_exists($date, $count["location"])) {
                $count["location"][$date] = 0;
            }
            $count["location"][$date]++;

            return $position;
        }, $locations);

        $finance_locations = $this->finances_service->getMarkers($from, $to);
        $finance_markers = array_map(function ($loc) use (&$count) {

            $position = $loc->getPosition();

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
            $dateFormatter = new \IntlDateFormatter($language);
            $dateFormatter->setPattern($dateFormatPHP['date']);
            $date = $dateFormatter->format(new \DateTime($position["dt"]));
            if (!array_key_exists($date, $count["finances"])) {
                $count["finances"][$date] = 0;
            }
            $count["finances"][$date]++;

            return $position;
        }, $finance_locations);

        $user_cars = $this->car_service->getUserElements();
        $carservice_locations = $this->car_service_service->getMarkers($from, $to, $user_cars);
        $carservice_markers = array_map(function ($loc) use (&$count) {

            $position = $loc->getPosition();

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
            $dateFormatter = new \IntlDateFormatter($language);
            $dateFormatter->setPattern($dateFormatPHP['date']);
            $date = $dateFormatter->format(new \DateTime($position["dt"]));
            if (!array_key_exists($date, $count["cars"])) {
                $count["cars"][$date] = 0;
            }
            $count["cars"][$date]++;

            return $position;
        }, $carservice_locations);

        $user_projects = $this->splitbill_group_service->getUserElements();
        $splitbill_locations = $this->splitbill_bill_service->getMarkers($from, $to, $user_projects);
        $splitbill_markers = array_map(function ($loc) use (&$count) {

            $position = $loc->getPosition();

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
            $dateFormatter = new \IntlDateFormatter($language);
            $dateFormatter->setPattern($dateFormatPHP['date']);
            $date = $dateFormatter->format(new \DateTime($position["dt"]));
            if (!array_key_exists($date, $count["splitbills"])) {
                $count["splitbills"][$date] = 0;
            }
            $count["splitbills"][$date]++;

            return $position;
        }, $splitbill_locations);

        $user_projects = $this->timesheet_project_service->getUserElements();
        $sheet_locations = $this->timesheet_sheet_service->getMarkers($from, $to, $user_projects);
        $sheet_markers = array_map(function ($loc) use (&$count) {

            $position = $loc->getPosition($this->translation, $this->settings);

            $date = $position["dt"];
            if (!array_key_exists($date, $count["timesheets"])) {
                $count["timesheets"][$date] = 0;
            }
            $count["timesheets"][$date]++;

            return $position;
        }, $sheet_locations);

        $user_trips = $this->trip_service->getUserElements();
        $trip_events_locations = $this->trip_event_service->getMarkers($from, $to, $user_trips);
        $trip_events_markers = array_map(function ($loc) use (&$count) {

            $position = $loc->getPosition();

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

            $dateFormatter = new \IntlDateFormatter($language);
            $timeFormatter = new \IntlDateFormatter($language);
            $datetimeFormatter = new \IntlDateFormatter($language);
            $dateFormatter->setPattern($dateFormatPHP['date']);
            $timeFormatter->setPattern($dateFormatPHP['time']);
            $datetimeFormatter->setPattern($dateFormatPHP['datetime']);

            $fromTranslation = $this->translation->getTranslatedString("FROM");
            $toTranslation = $this->translation->getTranslatedString("TO");

            $position['popup'] = $loc->createPopup($dateFormatter, $timeFormatter, $datetimeFormatter, $fromTranslation, $toTranslation, '<br/>', '<br/>', true);

            $date = $loc->createPopup($dateFormatter, $timeFormatter, $datetimeFormatter, $fromTranslation, $toTranslation, '', '', ' ', false);
            if (!array_key_exists($date, $count["trips"])) {
                $count["trips"][$date] = 0;
            }
            $count["trips"][$date]++;

            return $position;
        }, $trip_events_locations);

        $response_data = [
            "markers" => 
                array_merge(
                    $location_markers,
                    $finance_markers,
                    $carservice_markers,
                    $splitbill_markers,
                    $sheet_markers,
                    $trip_events_markers
                ),
            "count" => $count
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getLastLocationTime() {
        if ($this->current_user->getUser()->hasModule('location')) {
            $last_time = $this->mapper->getLastTime();
            $date = new \DateTime($last_time);

            return new Payload(Payload::$RESULT_JSON, ["status" => "success", "ts" => $date->getTimestamp()]);
        }
        return new Payload(Payload::$RESULT_JSON, ["status" => "error", "message" => "user has no location module"]);
    }

    public function getAddress($data) {

        $lat = array_key_exists('lat', $data) && !empty($data['lat']) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $lng = array_key_exists('lng', $data) && !empty($data['lng']) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $response_data = ['status' => 'error', 'data' => []];

        if (!is_null($lat) && !is_null($lng)) {

            $query = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $lat . '&lon=' . $lng;

            list($status, $result) = $this->helper->request($query);


            if ($status == 200) {
                $response_data['status'] = 'success';
                $array = json_decode($result, true);
                if (is_array($array) && array_key_exists("address", $array)) {
                    $response_data['data'] = $array["address"];
                }
            }
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }
}
