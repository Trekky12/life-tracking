<?php

namespace App\Application\Action\Workouts\Bodypart;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Workouts\Bodypart\BodypartWriter;

class BodypartSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, BodypartWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data);
        return $this->responder->respond($entry->withRouteName('workouts_bodyparts'));
    }

}
