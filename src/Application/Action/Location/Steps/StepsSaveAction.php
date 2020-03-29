<?php

namespace App\Application\Action\Location\Steps;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Location\Steps\StepsService;
use App\Application\Responder\RedirectResponder;

class StepsSaveAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, StepsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $date = $request->getAttribute('date');
        $data = $request->getParsedBody();

        $url_params = $this->service->saveSteps($date, $data);

        return $this->responder->respond('steps_stats_month', 301, true, $url_params);
    }

}
