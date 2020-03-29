<?php

namespace App\Application\Action\Main;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Main\MainService;
use App\Application\Responder\HTMLResponder;

class LogfileAction {

    private $responder;
    private $service;

    public function __construct(HTMLResponder $responder, MainService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        // GET Param 'days'
        $days = intval(filter_var($request->getQueryParam('days', 1), FILTER_SANITIZE_NUMBER_INT));

        $logfile = $this->service->getLogfile($days);
        return $this->responder->respond($logfile->withTemplate('main/logfile.twig'));
    }

}
