<?php

namespace App\Application\Action\Main;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\LogService;
use App\Application\Responder\HTMLTemplateResponder;

class LogfileAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, LogService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        // GET Param 'days'
        $days = intval(filter_var($request->getQueryParam('days', 1), FILTER_SANITIZE_NUMBER_INT));

        $logfileOverview = $this->service->getLogfileOverview($days);
        return $this->responder->respond($logfileOverview->withTemplate('main/logfile.twig'));
    }

}
