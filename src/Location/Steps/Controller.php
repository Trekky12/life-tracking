<?php

namespace App\Location\Steps;

use Slim\Http\Request as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $module = "location";

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
    }

    public function editSteps(Request $request, Response $response) {
        $date = $request->getAttribute('date');
        $steps = $this->mapper->getStepsOfDate($date);

        return $this->twig->render($response, 'location/steps/edit.twig', ['date' => $date, 'steps' => $steps > 0 ? $steps : 0]);
    }

    public function saveSteps(Request $request, Response $response) {
        $date = $request->getAttribute('date');

        $data = $request->getParsedBody();
        $steps_new = array_key_exists("steps", $data) ? filter_var($data["steps"], FILTER_SANITIZE_NUMBER_INT) : 0;

        $steps_old = $this->mapper->getStepsOfDate($date);

        // update
        if ($steps_old > 0) {
            $this->mapper->updateSteps($date, $steps_old, $steps_new);
        }
        // insert
        else {
            $this->mapper->insertSteps($date, $steps_new);
        }

        $dateObj = new \DateTime($date);
        $redirect_url = $this->router->pathFor('steps_stats_month', ['year' => $dateObj->format('Y'), 'month' => $dateObj->format('m')]);
        return $response->withRedirect($redirect_url, 301);
    }

    public function steps(Request $request, Response $response) {
        $steps = $this->mapper->getStepsPerYear();
        list($chart_data, $labels) = $this->createChartData($steps);
        return $this->twig->render($response, 'location/steps/steps.twig', ['stats' => $steps, "data" => $chart_data, "labels" => $labels]);
    }

    public function stepsYear(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $steps = $this->mapper->getStepsOfYear($year);
        list($chart_data, $labels) = $this->createChartData($steps, "month");
        return $this->twig->render($response, 'location/steps/steps_year.twig', ['stats' => $steps, "year" => $year, "data" => $chart_data, "labels" => $labels]);
    }

    public function stepsMonth(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $steps = $this->mapper->getStepsOfYearMonth($year, $month);
        list($chart_data, $labels) = $this->createChartData($steps, "date");
        return $this->twig->render($response, 'location/steps/steps_month.twig', ['stats' => $steps, "year" => $year, "month" => $month, "data" => $chart_data, "labels" => $labels]);
    }

    private function createChartData($stats, $key = "year") {
        $data = [];

        foreach ($stats as $el) {
            if (!array_key_exists($el[$key], $data)) {
                $data[$el[$key]] = [];
            }

            $data[$el[$key]] = $el["steps"];
        }

        $labels = array_keys($data);
        if ($key === "month") {
            $labels = array_map(function($l) {
                return $this->helper->getMonthName($l);
            }, $labels);
        }
        if ($key === "date") {
            $labels = array_map(function($l) {
                return $this->helper->getDay($l);
            }, $labels);
        }

        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);
        $labels = json_encode($labels, JSON_NUMERIC_CHECK);

        return array($data, $labels);
    }

}
