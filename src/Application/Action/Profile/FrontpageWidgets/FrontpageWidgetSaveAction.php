<?php

namespace App\Application\Action\Profile\FrontpageWidgets;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveJSONResponder;
use App\Domain\Home\Widget\WidgetWriter;

class FrontpageWidgetSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveJSONResponder $responder, WidgetWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $payload = $this->service->save(null, $data);
        return $this->responder->respond($payload);
    }

}
