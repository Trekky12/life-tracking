<?php

namespace App\Trips\Event;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Trips\Event\Event';
        $this->index_route = 'trips_view';
        $this->edit_template = 'trips/events/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->trip_mapper = new \App\Trips\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('trip');
        $trip = $this->trip_mapper->getFromHash($hash);

        $this->checkAccess($trip->id);

        $events = $this->mapper->getFromTrip($trip->id);

        return $this->ci->view->render($response, 'trips/events/index.twig', [
                    "events" => $events,
                    "trip" => $trip
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
        $this->preEdit($entry_id);

        return $this->ci->view->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'trip' => $trip,
                    'types' => self::eventTypes()
        ]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $trip = $request->getAttribute('trip');
        $data = $request->getParsedBody();
        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $this->insertOrUpdate($id, $data);

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
    protected function preSave($id, &$data) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preEdit($id) {
        $this->allowParentOwnerOnly($id);
    }

    protected function preDelete($id) {
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

}
