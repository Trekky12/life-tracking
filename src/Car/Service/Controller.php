<?php

namespace App\Car\Service;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Base\Controller {

    private $car_service;
    private $car_stats_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            CarServiceService $service,
            \App\Car\CarService $car_service,
            CarServiceStatsService $car_stats_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->car_service = $car_service;
        $this->car_stats_service = $car_stats_service;
    }

    public function index(Request $request, Response $response) {
        $data = $this->service->index();

        return $this->twig->render($response, 'cars/service/index.twig', $data);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        if (!is_null($entry_id) && !$this->service->hasAccessToCarOfEntry($entry_id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        // GET Param 'type'
        $type = $request->getParam('type');

        $entry = $this->service->getEntry($entry_id);

        $user_cars = $this->car_service->getUserCars();
        $cars = $this->car_service->getAllCarsOrderedByName();

        return $this->twig->render($response, 'cars/service/edit.twig', ['entry' => $entry, 'cars' => $cars, 'user_cars' => $user_cars, 'type' => $type]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $user_cars = $this->car_service->getUserCars();
        if (!array_key_exists("car", $data) || !in_array($data["car"], $user_cars)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $new_id = $this->doSave($id, $data, null);

        $this->service->calculateFuelConsumption($new_id);

        $redirect_url = $this->router->urlFor('car_service');
        return $response->withRedirect($redirect_url, 301);
    }

    public function stats(Request $request, Response $response) {

        $stats = $this->car_stats_service->stats();
        return $this->twig->render($response, 'cars/stats.twig', $stats);
    }

    public function setYearlyMileageCalcTyp(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $this->service->setCalculationType($data);

        $response_data = ['status' => 'success'];
        return $response->withJSON($response_data);
    }

    public function tableFuel(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        $response_data = $this->service->fuelTable($requestData);

        return $response->withJson($response_data);
    }

    public function tableService(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        $response_data = $this->service->serviceTable($requestData);

        return $response->withJson($response_data);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        
        if (!is_null($id) && !$this->service->hasAccessToCarOfEntry($id)) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

}
