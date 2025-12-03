<?php

namespace App\Application\Action\Workouts\Exercise;

use Slim\Http\ServerRequest as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Domain\Workouts\Exercise\ExerciseService;
use App\Application\Responder\JSONHTMLTemplateResponder;

class ExercisesDataAction {

    private $responder;
    private $service;

    public function __construct(JSONHTMLTemplateResponder $responder, ExerciseService $service) {
        $this->responder = $responder;
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response): Response {
        $data = $request->getQueryParams();
        $view = $request->getQueryParam('view');
        $payload = $this->service->getExercise($data, $view);

        $template = 'workouts/sessions/edit-single-exercise.twig';
        if ($view == 'create') {
            $template = 'workouts/sessions/create-single-exercise.twig';
        }

        return $this->responder->respond($payload->withTemplate($template));
    }
}
