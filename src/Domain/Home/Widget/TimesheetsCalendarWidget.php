<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Main\Utility\DateUtility;
use Slim\Routing\RouteParser;

class TimesheetsCalendarWidget implements Widget {

    private $logger;
    private $translation;
    private $router;
    private $current_user;
    private $project_service;
    private $sheet_mapper;
    private $projects = [];

    public function __construct(LoggerInterface $logger, Translator $translation, RouteParser $router, CurrentUser $user, ProjectService $project_service, SheetMapper $sheet_mapper) {
        $this->logger = $logger;
        $this->translation = $translation;
        $this->router = $router;
        $this->current_user = $user;
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

    public function getContent(WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];

        $from = (new \DateTime())->format('Y-m-d H:i:s');
        $to = (new \DateTime('tomorrow'))->format('Y-m-d H:i:s');

        $sheets = $this->sheet_mapper->getSheetForWidget($id, $from, $to);

        return $sheets;
    }

    public function getTitle(WidgetObject $widget = null) {
        $id = $widget->getOptions()["project"];
        return sprintf("%s ", $this->projects[$id]["name"]);
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
        return $this->router->urlFor('timesheets_calendar', ["project" => $this->projects[$id]["hash"]]);
    }
}
