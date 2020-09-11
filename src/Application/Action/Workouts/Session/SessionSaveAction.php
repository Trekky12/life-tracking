<?php

namespace App\Application\Action\Workouts\Session;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Workouts\Session\SessionWriter;

class SessionSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, SessionWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $plan_hash = $request->getAttribute("plan");
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $entry = $this->service->save($id, $data, ["plan" => $plan_hash]);
        return $this->responder->respond($entry->withRouteName('workouts_sessions')->withRouteParams(["plan" => $plan_hash]));
    }

}
