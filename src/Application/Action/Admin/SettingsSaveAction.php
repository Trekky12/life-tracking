<?php

namespace App\Application\Action\Admin;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Settings\SettingsService;

class SettingsSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, SettingsService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $entry = $this->service->save($data);
        return $this->responder->respond($entry->withRouteName('settings'));
    }

}
