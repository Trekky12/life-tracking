<?php

namespace App\Location;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Main\Utility\DateUtility;
use App\Main\Utility\Utility;

class Controller extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            LocationService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function index(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        // Filtered markers
        $hide = $request->getQueryParam('hide');

        list($from, $to) = DateUtility::getDateRange($requestData);

        $index = $this->service->index($from, $to, $hide);

        return $this->twig->render($response, 'location/index.twig', $index);
    }

    public function edit(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');
        $entry = $this->service->getEntry($entry_id);
        return $this->twig->render($response, 'location/edit.twig', ['entry' => $entry]);
    }

    public function getMarkers(Request $request, Response $response) {
        $requestData = $request->getQueryParams();
        list($from, $to) = DateUtility::getDateRange($requestData);
        $response_data = $this->service->getMarkers($from, $to);
        return $response->withJSON($response_data);
    }

    public function getAddress(Request $request, Response $response) {

        //$id = $request->getAttribute('id');

        $data = $request->getQueryParams();
        $lat = array_key_exists('lat', $data) && !empty($data['lat']) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $lng = array_key_exists('lng', $data) && !empty($data['lng']) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $response_data = $this->service->getAddress($lat, $lng);

        return $response->withJson($response_data);
    }

    public function record(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        try {
            $new_id = $this->doSave($id, $data, null);
        } catch (\Exception $e) {
            $this->logger->addError("Save API " . $this->service->getDataObject(), array("error" => $e->getMessage()));

            $response_data = array('status' => 'error', "error" => $e->getMessage());
            return $response->withJSON($response_data);
        }

        $response_data = array('status' => 'success');
        return $response->withJSON($response_data);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        if (!array_key_exists("device", $data)) {
            $data["device"] = Utility::getAgent();
        }

        $new_id = $this->doSave($id, $data, null);

        $redirect_url = $this->router->urlFor('location');
        return $response->withRedirect($redirect_url, 301);
    }
    
    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
