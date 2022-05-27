<?php

namespace App\Application\Action\Profile\FrontpageWidgets;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Home\HomeService;
use App\Application\Responder\JSONHTMLTemplateResponder;

class FrontpageWidgetDataAction {

    private $responder;
    private $service;

    public function __construct(JSONHTMLTemplateResponder $responder, HomeService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $widget = $request->getQueryParam('widget');
        $widget_options = $request->getQueryParam('options');
        $payload = $this->service->getWidgetData($id, $widget, $widget_options);
        return $this->responder->respond($payload);
    }

}
