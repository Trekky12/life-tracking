<?php

namespace App\Application\Action\Profile\FrontpageWidgets;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Home\HomeService;
use App\Application\Responder\JSONResultResponder;

class FrontpageWidgetUpdatePositionAction {

    private $responder;
    private $service;

    public function __construct(JSONResultResponder $responder, HomeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->updatePosition($data);
        return $this->responder->respond($payload);
    }

}
