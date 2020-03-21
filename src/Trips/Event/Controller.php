<?php

namespace App\Trips\Event;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Trips\TripService;
use App\Main\Utility\DateUtility;

class Controller extends \App\Base\Controller {

    private $trip_service;
    private $trip_event_image_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            TripEventService $service,
            TripService $trip_service,
            TripEventImageService $trip_event_image_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->trip_service = $trip_service;
        $this->trip_event_image_service = $trip_event_image_service;
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_service->getFromHash($hash);

        if (!$this->trip_service->isMember($trip->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($data, null, null);

        $response_data = $this->service->getTripEvents($trip, $from, $to);

        return $this->twig->render($response, 'trips/events/index.twig', $response_data);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_service->getFromHash($hash);

        if (!$this->trip_service->isMember($trip->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);

        $data = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($data, null, null);

        return $this->twig->render($response, 'trips/events/edit.twig', [
                    'entry' => $entry,
                    'trip' => $trip,
                    'types' => $this->service->getEventTypes(),
                    'from' => $from,
                    'to' => $to,
                    "isTripEventEdit" => true
        ]);
    }

    public function getMarkers(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_service->getFromHash($hash);

        if (!$this->trip_service->isMember($trip->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($data, null, null);

        $markers = $this->service->getMarkers($trip, $from, $to);

        return $response->withJSON($markers);
    }

    public function getLatLng(Request $request, Response $response) {

        $data = $request->getQueryParams();
        $address = array_key_exists('address', $data) && !empty($data['address']) ? filter_var($data['address'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $response_data = ['status' => 'error', 'data' => []];

        if (!is_null($address)) {

            $result = $this->service->getLocation($address);
            if ($result !== false) {
                $response_data['status'] = 'success';
                $response_data['data'] = $result;
            }
        }

        return $response->withJson($response_data);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $trip_hash = $request->getAttribute("trip");
        $trip = $this->trip_service->getFromHash($trip_hash);

        if (!$this->trip_service->isMember($trip->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data['trip'] = $trip->id;

        $new_id = $this->doSave($id, $data, null);

        $redirect_url = $this->router->urlFor('trips_view', ["trip" => $trip_hash]);
        return $response->withRedirect($redirect_url, 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        $trip_hash = $request->getAttribute("trip");
        $trip = $this->trip_service->getFromHash($trip_hash);

        if (!$this->trip_service->isMember($trip->id)) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

    public function image(Request $request, Response $response) {

        $event_id = $request->getAttribute('id');

        $response_data = ["status" => "error"];
        $files = $request->getUploadedFiles();

        if (!array_key_exists('image', $files) || empty($files['image'])) {
            $this->logger->addError("Update Event Image, Image Error", array("id" => $event_id, "files" => $files));

            $response_data["status"] = "error";
        }

        $image = $files['image'];

        $thumbnail = $this->trip_event_image_service->saveImage($event_id, $image);

        if ($thumbnail !== false) {

            $this->logger->addNotice("Update Event Image, Image Set", array("id" => $event_id, "image" => $image->getClientFilename()));

            $response_data["status"] = "success";
            $response_data["thumbnail"] = $thumbnail;
        } else {
            $response_data["status"] = "error";

            $this->logger->addNotice("Update Event Image, No File", array("id" => $event_id));
        }

        return $response->withJson($response_data);
    }

    public function image_delete(Request $request, Response $response) {

        $event_id = $request->getAttribute('id');
        $this->trip_event_image_service->deleteImage($event_id);

        $this->logger->addNotice("Delete Event Image", array("id" => $event_id));

        $response_data = ["status" => "success"];
        return $response->withJson($response_data);
    }

    public function updatePosition(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {

            if (array_key_exists("events", $data) && !empty($data["events"])) {

                $events = filter_var_array($data["events"], FILTER_SANITIZE_NUMBER_INT);
                $this->service->changePosition($events);

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
