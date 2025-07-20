<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\DateUtility;

class ProjectService extends Service {

    private $sheet_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ProjectMapper $mapper,
        SheetMapper $sheet_mapper
    ) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->sheet_mapper = $sheet_mapper;
    }

    public function index($archive = 0) {
        $projects = $this->mapper->getUserItems('t.createdOn DESC, name', false, null, $archive);

        $times = $this->sheet_mapper->getTimes($projects);

        $new_times = [];

        foreach ($times as $project_id => $time) {

            if (array_key_exists($project_id, $projects)) {

                $project = $projects[$project_id];

                $new_time = DateUtility::splitDateInterval($time["sum"]);
                if ($project->has_duration_modifications > 0 && $time["sum"] > 0) {
                    $new_time = DateUtility::splitDateInterval($time["sum_modified"]) . ' (' . $new_time . ')';
                }

                $new_times[$project_id] = $new_time;
            }
        }

        return new Payload(Payload::$RESULT_HTML, [
            'projects' => $projects, 
            'times' => $new_times,
            'archive' => $archive
        ]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'isTimesheetProject' => true,
            'units' => Project::getUnits()
        ]);
    }

    public function getUserProjects() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getProjects() {
        return $this->mapper->getAll();
    }

    public function getProject($id) {
        return $this->mapper->get($id);
    }
}
