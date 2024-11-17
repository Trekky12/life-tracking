<?php

namespace App\Domain\Timesheets\NoticeField;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;

class NoticeFieldService extends Service {

    private $project_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, NoticeFieldMapper $mapper, ProjectService $project_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
    }

    public function index($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $fields = $this->mapper->getFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, [
            'fields' => $fields, 
            "project" => $project
        ]);
    }

    public function edit($hash, $entry_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($project->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            "entry" => $entry,
            "project" => $project,
            "dataTypes" => $this->getDataTypes()
        ]);
    }

    private function getDataTypes() {
        return [
            'textfield' => 'DATATYPE_TEXTFIELD',
            'textarea' => 'DATATYPE_TEXTAREA',
            'select' => 'DATATYPE_SELECT',
            'html' => 'DATATYPE_HTML'
        ];
    }

    public function getNoticeFields($project, $type = 'sheet'){
        return $this->mapper->getFromProject($project, $type);
    }

}
