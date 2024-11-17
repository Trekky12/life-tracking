<?php

namespace App\Domain\Timesheets\ProjectNotice;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\NoticeField\NoticeFieldService;
use App\Application\Payload\Payload;

class ProjectNoticeService extends Service {

    protected $project_service;
    protected $noticefield_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ProjectNoticeMapper $mapper,
        ProjectService $project_service,
        NoticeFieldService $noticefield_service
    ) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->noticefield_service = $noticefield_service;
    }

    public function edit($hash, $requestData) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        $fields = $this->noticefield_service->getNoticeFields($project->id, 'project');

        $response_data = [
            'project' => $project,
            'hasTimesheetNotice' => true,
            'fields' => $fields
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getData($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->mapper->getNotice($project->id);

        $response_data = [
            'entry' => $entry
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }
}
