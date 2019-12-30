<?php

namespace App\Trips\Event;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Trips\Event\Event';
    protected $parent_model = '\App\Trips\Trip';
    protected $index_route = 'trips_view';
    protected $edit_template = 'trips/events/edit.twig';
    protected $element_view_route = 'trips_view';
    protected $module = "trips";
    private $trip_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->trip_mapper = new \App\Trips\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_mapper->getFromHash($hash);

        $this->checkAccess($trip->id);

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data, null, null);

        // always show all events (hide the one not in range)
        $events = $this->mapper->getFromTrip($trip->id, null, null, "start_date, start_time, end_date, end_time");

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

        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmt2 = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmt->setPattern($dateFormatPHP["trips_buttons"]);
        $fmt2->setPattern($dateFormatPHP["trips_list"]);

        $dateRange = [];
        $dateRange['all'] = ["date" => null, "display_date" => $this->ci->get('helper')->getTranslatedString("TRIPS_OVERVIEW"), "events" => []];
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


        $dateFormatter = new \IntlDateFormatter($langugage, NULL, NULL);
        $timeFormatter = new \IntlDateFormatter($langugage, NULL, NULL);
        $datetimeFormatter = new \IntlDateFormatter($langugage, NULL, NULL);
        $dateFormatter->setPattern($dateFormatPHP['date']);
        $timeFormatter->setPattern($dateFormatPHP['time']);
        $datetimeFormatter->setPattern($dateFormatPHP['datetime']);

        $fromTranslation = $this->ci->get('helper')->getTranslatedString("FROM");
        $toTranslation = $this->ci->get('helper')->getTranslatedString("TO");

        foreach ($events as $ev) {
            
            if(empty($ev->start_date)){
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

        return $this->ci->view->render($response, 'trips/events/index.twig', [
                    "events" => $events,
                    "trip" => $trip,
                    "isTrips" => true,
                    "from" => $from,
                    "to" => $to,
                    "range" => $dateRange,
        ]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_mapper->getFromHash($hash);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        $this->preEdit($entry_id, $request);

        return $this->ci->view->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'trip' => $trip,
                    'types' => self::eventTypes(),
                    'from' => $from,
                    'to' => $to
        ]);
    }

    public function getMarkers(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_mapper->getFromHash($hash);

        $this->checkAccess($trip->id);

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data, null, null);

        $events = $this->mapper->getFromTrip($trip->id, $from, $to);


        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];

        $dateFormatter = new \IntlDateFormatter($langugage, NULL, NULL);
        $timeFormatter = new \IntlDateFormatter($langugage, NULL, NULL);
        $datetimeFormatter = new \IntlDateFormatter($langugage, NULL, NULL);
        $dateFormatter->setPattern($dateFormatPHP['date']);
        $timeFormatter->setPattern($dateFormatPHP['time']);
        $datetimeFormatter->setPattern($dateFormatPHP['datetime']);

        $fromTranslation = $this->ci->get('helper')->getTranslatedString("FROM");
        $toTranslation = $this->ci->get('helper')->getTranslatedString("TO");

        $markers = [];
        foreach ($events as $ev) {
            // create Popup
            $ev->createPopup($dateFormatter, $timeFormatter, $datetimeFormatter, $fromTranslation, $toTranslation, '<br/>', '<br/>');
            $markers[] = $ev->getPosition();
        }

        return $response->withJSON($markers);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $trip = $request->getAttribute('trip');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data, $request);

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route, ["trip" => $trip]), 301);
    }

    public function getLatLng(Request $request, Response $response) {

        $data = $request->getQueryParams();
        $address = array_key_exists('address', $data) && !empty($data['address']) ? filter_var($data['address'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $newResponse = ['status' => 'error', 'data' => []];

        if (!is_null($address)) {

            $query = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);

            list($status, $result) = $this->ci->get('helper')->request($query);

            if ($status == 200) {
                $newResponse['status'] = 'success';
                $result = json_decode($result, true);
                if (is_array($result)) {
                    $newResponse['data'] = $result;
                }
            }
        }

        return $response->withJson($newResponse);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowParentOwnerOnly($id);
    }

    private function allowParentOwnerOnly($element_id) {
        $user = $this->ci->get('helper')->getUser()->id;
        if (!is_null($element_id)) {
            $element = $this->mapper->get($element_id);
            $trip = $this->trip_mapper->get($element->trip);

            if ($trip->user !== $user) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Is the user allowed to view event overview
     */
    private function checkAccess($id) {
        $trip_users = $this->trip_mapper->getUsers($id);
        $user = $this->ci->get('helper')->getUser()->id;
        if (!in_array($user, $trip_users)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    public static function eventTypes() {
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
        $trip = $this->getParentObjectMapper()->get($entry->getParentID());
        $this->element_view_route_params["trip"] = $trip->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->trip_mapper;
    }

}
