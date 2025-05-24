<?php

namespace App\Application\Action\Trips\Event;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Trips\Event\TripEventImageService;
use App\Application\Responder\ImageResponder;

class EventImageSaveAction {

    private $responder;
    private $service;

    public function __construct(ImageResponder $responder, TripEventImageService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $event_id = $request->getAttribute('id');
        /**
         * Handle uploaded file
         * @link https://akrabat.com/psr-7-file-uploads-in-slim-3/
         */
        $files = $request->getUploadedFiles();
        $payload = $this->service->saveImage($event_id, $files);

        return $this->responder->respond($payload);
    }

}
