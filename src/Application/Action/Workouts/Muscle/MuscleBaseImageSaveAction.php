<?php

namespace App\Application\Action\Workouts\Muscle;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Muscle\MuscleService;
use App\Application\Responder\RedirectResponder;

class MuscleBaseImageSaveAction {

    private $responder;
    private $service;

    public function __construct(RedirectResponder $responder, MuscleService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        /**
         * Handle uploaded file
         * @link https://akrabat.com/psr-7-file-uploads-in-slim-3/
         */
        $files = $request->getUploadedFiles();
        $payload = $this->service->updateBaseImage($data, $files);

        return $this->responder->respond('workouts_muscles', 301);
    }

}
