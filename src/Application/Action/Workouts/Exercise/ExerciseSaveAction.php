<?php

namespace App\Application\Action\Workouts\Exercise;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Responder\SaveResponder;
use App\Domain\Workouts\Exercise\ExerciseWriter;

class ExerciseSaveAction {

    private $responder;
    private $service;

    public function __construct(SaveResponder $responder, ExerciseWriter $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
                
        $files = $request->getUploadedFiles();
        
        $entry = $this->service->save($id, $data, ["files" => $files]);
        return $this->responder->respond($entry->withRouteName('workouts_exercises'));
    }

}
