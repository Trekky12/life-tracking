<?php

namespace App\Domain\Home\Widget;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Main\Utility\DateUtility;

class TimesheetsSumWidget {

    private $logger;
    private $current_user;
    private $project_service;
    private $sheet_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ProjectService $project_service, SheetMapper $sheet_mapper) {
        $this->logger = $logger;
        $this->current_user = $user;
        $this->project_service = $project_service;
        $this->sheet_mapper = $sheet_mapper;
    }

    public function getContent() {
        $user_projects = $this->project_service->getUserProjects();

        $projects = $this->project_service->getProjects();

        $result = [];
        foreach ($user_projects as $project_id) {
            $project = $projects[$project_id];
            
            $range = $this->sheet_mapper->getMinMaxDate("start", "end");
            $totalSeconds = $this->sheet_mapper->tableSum($project->id, $range["min"], $range["max"]);
            
            $result[$project_id] = ["name" => $project->name, "sum" => DateUtility::splitDateInterval($totalSeconds)];

        }

        return $result;
    }

}
