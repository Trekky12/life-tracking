<?php

namespace App\Domain\Timesheets\SheetNotice;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Application\Payload\Payload;

class SheetNoticeService extends Service {

    protected $project_service;
    protected $sheet_service;
    protected $user_service;
    protected $settings;
    protected $router;
    protected $translation;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SheetNoticeMapper $mapper,
            ProjectService $project_service,
            SheetService $sheet_service) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->sheet_service = $sheet_service;
    }

    public function edit($hash, $sheet_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $sheet = $this->sheet_service->getEntry($sheet_id);

        $entry = $this->mapper->getNotice($sheet_id);

        $response_data = [
            'sheet' => $sheet,
            'project' => $project,
            'hasTimesheetNotice' => true
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getData($hash, $sheet_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->mapper->getNotice($sheet_id);

        $response_data = [
            'entry' => $entry
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
