<?php

namespace App\Application\Action\Workouts\Plan;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Workouts\Plan\PlanWriter;

class PlanSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, PlanWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();        
        var_dump($data);
        die();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('workouts_plans'));
    }

}
