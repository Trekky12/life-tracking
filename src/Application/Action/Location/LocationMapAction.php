<?php

namespace App\Application\Action\Location;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\LocationService;
use App\Application\Responder\HTMLTemplateResponder;
use App\Domain\Main\Utility\DateUtility;

class LocationMapAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, LocationService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $requestData = $request->getQueryParams();

        // Filtered markers
        $hide = $request->getQueryParam('hide');

        $index = $this->service->index($hide, $requestData);

        return $this->responder->respond($index->withTemplate('location/index.twig'));
    }

}
