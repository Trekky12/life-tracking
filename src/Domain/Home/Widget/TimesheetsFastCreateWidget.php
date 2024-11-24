<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetMapper;
use Slim\Routing\RouteParser;

class TimesheetsFastCreateWidget implements Widget {

    private $translation;
    private $router;
    private $project_service;
    private $projects = [];

    public function __construct(Translator $translation, RouteParser $router, ProjectService $project_service) {
        $this->translation = $translation;
        $this->router = $router;
        $this->project_service = $project_service;

        $this->projects = $this->createList();
    }

    private function createList() {
        $user_projects = $this->project_service->getUserProjects();

        $projects = $this->project_service->getProjects();

        $result = [];
        foreach ($user_projects as $project_id) {
            $project = $projects[$project_id];
            
            $result[$project_id] = ["name" => $project->name, "hash" => $project->getHash() ];
        }

        return $result;
    }

    public function getListItems() {
        return array_keys($this->projects);
    }

    public function getContent(WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];
        
        $project = $this->project_service->getEntry($id);
        return $project;
    }

    public function getTitle(WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];
        $project = $this->project_service->getEntry($id);
        
        return sprintf("%s | %s", $project->is_day_based ? $this->translation->getTranslatedString("TIMESHEETS_FAST_DAY_BASED"):$this->translation->getTranslatedString("TIMESHEETS_FAST_PROJECT_BASED"), $this->projects[$id]["name"]);
    }

    public function getOptions(WidgetObject $widget = null) {
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

    public function getLink(WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];
        return $this->router->urlFor('timesheets_fast', ["project" => $this->projects[$id]["hash"]]);
    }

}
