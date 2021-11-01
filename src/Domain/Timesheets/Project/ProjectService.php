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

    private $user_service;
    private $sheet_mapper;
    private $translation;

    public function __construct(LoggerInterface $logger, 
            CurrentUser $user, 
            ProjectMapper $mapper, 
            UserService $user_service, 
            SheetMapper $sheet_mapper,
            Translator $translation) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->sheet_mapper = $sheet_mapper;
        $this->translation = $translation;
    }

    public function index() {
        $projects = $this->mapper->getUserItems('t.createdOn DESC, name');

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

        return new Payload(Payload::$RESULT_HTML, ['projects' => $projects, 'times' => $new_times]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

    public function getUserProjects() {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->getElementsOfUser($user);
    }

    public function getProjects() {
        return $this->mapper->getAll();
    }

    public function checkPassword($hash, $data) {
        $project = $this->getFromHash($hash);

        if (!$this->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        
        $password = array_key_exists('password', $data) ? filter_var($data['password'], FILTER_SANITIZE_STRING) : null;

        if (is_null($project->password)) {
            return new Payload(Payload::$RESULT_JSON, ["status" => "error", "reason" => $this->translation->getTranslatedString("TIMESHEETS_PROJECT_PASSWORD_SHEETS_NOTICES_NOT_SET")]);
        }
        if (password_verify($password, $project->password)) {
            return new Payload(Payload::$RESULT_JSON, ["status" => "success", "data" => $project->salt]);
        }
        return new Payload(Payload::$RESULT_JSON, ["status" => "error", "reason" => $this->translation->getTranslatedString("TIMESHEETS_PROJECT_PASSWORD_SHEETS_NOTICES_WRONG")]);
    }

}
