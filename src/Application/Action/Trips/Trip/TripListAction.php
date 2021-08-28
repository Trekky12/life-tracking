<?php

namespace App\Application\Action\Trips\Trip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\TripService;
use App\Application\Responder\HTMLTemplateResponder;

class TripListAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, TripService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        
        $filter = $request->getParam('filter');
        
        $index = $this->service->index($filter);
        
        return $this->responder->respond($index->withTemplate('trips/index.twig'));
    }

}
