<?php

namespace App\Location\Steps;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;

class Controller extends \App\Base\Controller {

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            StepsService $service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
    }

    public function steps(Request $request, Response $response) {
        $data = $this->service->getStepsPerYear();
        return $this->twig->render($response, 'location/steps/steps.twig', $data);
    }

    public function stepsYear(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $data = $this->service->getStepsOfYear($year);
        return $this->twig->render($response, 'location/steps/steps_year.twig', $data);
    }

    public function stepsMonth(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $data = $this->service->getStepsOfYearMonth($year, $month);
        return $this->twig->render($response, 'location/steps/steps_month.twig', $data);
    }

    public function editSteps(Request $request, Response $response) {
        $date = $request->getAttribute('date');

        $steps = $this->service->getStepsOfDate($date);

        return $this->twig->render($response, 'location/steps/edit.twig', ['date' => $date, 'steps' => $steps > 0 ? $steps : 0]);
    }

    public function saveSteps(Request $request, Response $response) {
        $date = $request->getAttribute('date');

        $data = $request->getParsedBody();
        $steps_new = array_key_exists("steps", $data) ? filter_var($data["steps"], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->service->updateSteps($date, $steps_new);

        $dateObj = new \DateTime($date);
        $redirect_url = $this->router->urlFor('steps_stats_month', ['year' => $dateObj->format('Y'), 'month' => $dateObj->format('m')]);
        return $response->withRedirect($redirect_url, 301);
    }

}
