<?php

namespace App\Trips\Event;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;
use Intervention\Image\ImageManagerStatic as Image;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Trips\Event\Event';
    protected $parent_model = '\App\Trips\Trip';
    protected $index_route = 'trips_view';
    protected $edit_template = 'trips/events/edit.twig';
    protected $element_view_route = 'trips_view';
    protected $module = "trips";
    private $trip_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->trip_mapper = new \App\Trips\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_mapper->getFromHash($hash);

        $this->checkAccess($trip->id);

        $data = $request->getQueryParams();
        list($from, $to) = $this->helper->getDateRange($data, null, null);

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

        $language = $this->settings['app']['i18n']['php'];
        $dateFormatPHP = $this->settings['app']['i18n']['dateformatPHP'];

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

        $mapbox_token = $this->settings['app']['mapbox_token'];

        return $this->twig->render($response, 'trips/events/index.twig', [
                    "events" => $events,
                    "trip" => $trip,
                    "isTrips" => true,
                    "from" => $from,
                    "to" => $to,
                    "range" => $dateRange,
                    "mapbox_token" => $mapbox_token
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
        list($from, $to) = $this->helper->getDateRange($data, null, null);

        $this->preEdit($entry_id, $request);

        return $this->twig->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'trip' => $trip,
                    'types' => self::eventTypes(),
                    'from' => $from,
                    'to' => $to,
                    "isTripEventEdit" => true
        ]);
    }

    public function getMarkers(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_mapper->getFromHash($hash);

        $this->checkAccess($trip->id);

        $data = $request->getQueryParams();
        list($from, $to) = $this->helper->getDateRange($data, null, null);

        $events = $this->mapper->getFromTrip($trip->id, $from, $to, "start_date, start_time, end_date, end_time, position");


        $language = $this->settings['app']['i18n']['php'];
        $dateFormatPHP = $this->settings['app']['i18n']['dateformatPHP'];

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

        return $response->withJSON($markers);
    }

    public function getLatLng(Request $request, Response $response) {

        $data = $request->getQueryParams();
        $address = array_key_exists('address', $data) && !empty($data['address']) ? filter_var($data['address'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $response_data = ['status' => 'error', 'data' => []];

        if (!is_null($address)) {

            $query = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);

            list($status, $result) = $this->helper->request($query);

            if ($status == 200) {
                $response_data['status'] = 'success';
                $result = json_decode($result, true);
                if (is_array($result)) {
                    $response_data['data'] = $result;
                }
            }
        }

        return $response->withJson($response_data);
    }

    protected function afterSave($id, array $data, Request $request) {
        $entry = $this->mapper->get($id);
        $trip_id = $entry->trip;
        $trip = $this->trip_mapper->get($trip_id);
        $this->index_params = ["trip" => $trip->getHash()];
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $trip_hash = $request->getAttribute("trip");
        $entry = $this->trip_mapper->getFromHash($trip_hash);
        $this->checkAccess($entry->id);

        $data['trip'] = $entry->id;
    }

    protected function preEdit($id, Request $request) {
        $trip_hash = $request->getAttribute("trip");
        $entry = $this->trip_mapper->getFromHash($trip_hash);
        $this->checkAccess($entry->id);
    }

    protected function preDelete($id, Request $request) {
        $trip_hash = $request->getAttribute("trip");
        $entry = $this->trip_mapper->getFromHash($trip_hash);
        $this->checkAccess($entry->id);
    }

    /**
     * Is the user allowed to view event overview
     */
    private function checkAccess($id) {
        $trip_users = $this->trip_mapper->getUsers($id);
        $user = $this->user_helper->getUser()->id;
        if (!in_array($user, $trip_users)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
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

    public function image(Request $request, Response $response) {

        $user = $this->user_helper->getUser();

        $entry_id = $request->getAttribute('id');
        $entry = $this->mapper->get($entry_id);

        $response_data = ["status" => "error"];
        $files = $request->getUploadedFiles();

        if (!array_key_exists('image', $files) || empty($files['image'])) {
            $this->logger->addError("Update Event Image, Image Error", array("user" => $user->id, "id" => $entry_id, "files" => $files));

            $response_data["status"] = "error";
        }

        $image = $files['image'];

        if ($image->getError() === UPLOAD_ERR_OK) {

            $folder = $this->settings['app']['upload_folder'];

            $uploadFileName = $image->getClientFilename();
            $file_extension = pathinfo($uploadFileName, PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($uploadFileName, PATHINFO_FILENAME);
            $file_name = hash('sha256', time() . rand(0, 1000000) . $user->id) . '_' . $file_wo_extension;
            $complete_file_name = $folder . '/events/' . $file_name;

            $image->moveTo($complete_file_name . '.' . $file_extension);
            /**
             * Create Thumbnail
             */
            $img = Image::make($complete_file_name . '.' . $file_extension);
            /**
             * @link http://image.intervention.io/api/resize
             */
            $img->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($complete_file_name . '-small.' . $file_extension);

            $this->mapper->update_image($entry->id, $file_name . '.' . $file_extension);

            $this->logger->addNotice("Update Event Image, Image Set", array("user" => $user->id, "id" => $entry_id, "image" => $file_name . '.' . $file_extension));

            $response_data["status"] = "success";
            $response_data["thumbnail"] = "/" . $folder . "/events/" . $file_name . '-small.' . $file_extension;
        } else if ($image->getError() === UPLOAD_ERR_NO_FILE) {
            $response_data["status"] = "error";

            $this->logger->addNotice("Update Event Image, No File", array("user" => $user->id, "id" => $entry_id));
        }

        return $response->withJson($response_data);
    }

    public function image_delete(Request $request, Response $response) {

        $user = $this->user_helper->getUser();

        $entry_id = $request->getAttribute('id');
        $entry = $this->mapper->get($entry_id);

        $folder = $this->settings['app']['upload_folder'];
        $thumbnail = $entry->get_thumbnail('small');
        $image = $entry->get_image();
        unlink($folder . '/events/' . $thumbnail);
        unlink($folder . '/events/' . $image);

        $this->mapper->update_image($entry->id, null);

        $this->logger->addNotice("Delete Event Image", array("user" => $user->id, "id" => $entry_id));

        $response_data = ["status" => "success"];
        return $response->withJson($response_data);
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {

            $user = $this->user_helper->getUser()->id;
            //$user_cards = $this->board_mapper->getUserCards($user);

            if (array_key_exists("events", $data) && !empty($data["events"])) {

                foreach ($data['events'] as $position => $item) {
                    //if (in_array($item, $user_cards)) {
                    $this->mapper->updatePosition($item, $position, $user);
                    //}
                }
                $response_data = ["status" => "success"];
                return $response->withJSON($response_data);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Update Event Position", array("data" => $data, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ["status" => "error"];
        return $response->withJSON($response_data);
    }

}
