<?php

namespace App\Application\Action\Car\Service;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Car\Service\CarServiceService;
use App\Application\Responder\HTMLTemplateResponder;

class RefuelEditAction {

    private $responder;
    private $service;

    public function __construct(HTMLTemplateResponder $responder, CarServiceService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $hash = $request->getAttribute('car');
        $entry_id = $request->getAttribute('id');
        $data = $this->service->edit($hash, $entry_id);
        return $this->responder->respond($data->withTemplate('cars/refuel/edit.twig'));
    }

}
