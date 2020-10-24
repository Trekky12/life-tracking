<?php

namespace App\Application\Action\Workouts\Template;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Workouts\Plan\PlanWriter;

class TemplateSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, PlanWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();   
        
        $data["is_template"] = 1;
        
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('workouts_templates'));
    }

}
