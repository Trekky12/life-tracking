<?php

namespace App\Application\Action\Location;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\LocationService;
use App\Application\Responder\HTMLResponder;
use App\Domain\Main\Utility\DateUtility;

class LocationMapAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, LocationService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $requestData = $request->getQueryParams();

        // Filtered markers
        $hide = $request->getQueryParam('hide');

        list($from, $to) = DateUtility::getDateRange($requestData);

        $index = $this->service->index($from, $to, $hide);

        return $this->responder->respond($index->withTemplate('location/index.twig'));
    }

}
