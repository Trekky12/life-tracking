<?php

namespace App\Domain\Home\Widget;

use App\Domain\Main\Translator;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Main\Utility\DateUtility;
use Slim\Routing\RouteParser;

class TimesheetsSumWidget implements Widget {

    private $translation;
    private $router;
    private $project_service;
    private $sheet_mapper;
    private $projects = [];

    public function __construct(Translator $translation, RouteParser $router,  ProjectService $project_service, SheetMapper $sheet_mapper) {
        $this->translation = $translation;
        $this->router = $router;
        $this->project_service = $project_service;
        $this->sheet_mapper = $sheet_mapper;

        $this->projects = $this->createList();
    }

    private function createList() {
        $user_projects = $this->project_service->getUserProjects();

        $projects = $this->project_service->getProjects();

        $result = [];
        foreach ($user_projects as $project_id) {
            $project = $projects[$project_id];
            $result[$project_id] = ["name" => $project->name, "hash" => $project->getHash()];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->projects);
    }

    public function getContent(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];

        $project = $this->project_service->getProject($id);

        $categories = [];
        $billed = null;
        $payed = null;
        $planned = null;
        $customer = null;

        $range = $this->sheet_mapper->getMinMaxDate("start", "end", $id, "project");
        $totalSeconds = $this->sheet_mapper->tableSum($id, $range["min"], $range["max"], $categories, $billed, $payed, $planned, $customer);

        $sum = DateUtility::splitDateInterval($totalSeconds);
        if ($project->has_duration_modifications > 0 && $totalSeconds > 0) {
            $totalSecondsModified = $this->sheet_mapper->tableSum($id, $range["min"], $range["max"], $categories, $billed, $payed, $planned, $customer, "%", "t.duration_modified");
            $sum = DateUtility::splitDateInterval($totalSecondsModified) . ' (' . $sum . ')';
        }

        return !empty($sum) ? $sum : "00:00:00";
    }

    public function getTitle(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];
        return sprintf("%s | %s", $this->translation->getTranslatedString("TIMESHEETS"), $this->projects[$id]["name"]);
    }

    public function getOptions(?WidgetObject $widget = null) {
        return [
            [
                "label" => $this->translation->getTranslatedString("TIMESHEETS_PROJECTS"),
                "data" => $this->createList(),
                "value" => !is_null($widget) ? $widget->getOptions()["project"] : null,
                "name" => "project",
                "type" => "select"
            ]
        ];
    }

    public function getLink(?WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];
        return $this->router->urlFor('timesheets_sheets', ["project" => $this->projects[$id]["hash"]]);
    }
}
