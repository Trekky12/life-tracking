<?php

namespace App\Application\Action\Location\Steps;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\Steps\StepsService;
use App\Application\Responder\HTMLResponder;

class StepsEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, StepsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $entry_id = $request->getAttribute('id');
        $date = $request->getAttribute('date');

        $data = $this->service->getStepsOfDate($date);

        return $this->responder->respond($data->withTemplate('location/steps/edit.twig'));
    }

}
