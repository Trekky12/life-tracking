<?php

namespace App\Domain\Trips\Route;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Trips\TripService;
use App\Application\Payload\Payload;
use Slim\Routing\RouteParser;

class RouteService extends Service {

    private $trip_service;
    private $router;

    public function __construct(LoggerInterface $logger, CurrentUser $user, RouteMapper $mapper, TripService $trip_service, RouteParser $router) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->trip_service = $trip_service;
        $this->router = $router;
    }

    public function getData($trip_hash) {

        $trip = $this->trip_service->getFromHash($trip_hash);

        if (!$this->trip_service->isMember($trip->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $results = $this->mapper->getFromTrip($trip->id);

        // add delete link
        $response_data = array_map(function($route) use ($trip_hash) {
            $route["delete"] = $this->router->urlFor('trips_delete_route', ['id' => $route["id"], 'trip' => $trip_hash]);
            return $route;
        }, $results);

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getWaypoints($trip_hash, $route_id) {

        $trip = $this->trip_service->getFromHash($trip_hash);

        if (!$this->trip_service->isMember($trip->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = ["waypoints" => [], "start_date" => null, "end_date" => null];
        try {
            $route = $this->mapper->get($route_id);
            $response_data["waypoints"] = $route->getWaypoints();
            $response_data["start_date"] = $route->start_date;
            $response_data["end_date"] = $route->end_date;
        } catch (\Exception $e) {
            // element not found?
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
