<?php

namespace App\Domain\Timesheets\ProjectNotice;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Customer\CustomerService;

class ProjectNoticeWriter extends ObjectActivityWriter {

    private $project_service;
    private $project_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        ProjectNoticeMapper $mapper,
        ProjectMapper $project_mapper,
        ProjectService $project_service
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->project_mapper = $project_mapper;
        $this->project_service = $project_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['project'] = $project->id;

        $payload = parent::save(null, $data, $additionalData);

        $entry = $payload->getResult();

        return $payload->withEntry(["changedOn" => $entry->changedOn]);
    }

    public function getParentMapper() {
        return $this->project_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_project_notice_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $project = $this->getParentMapper()->get($entry->getParentID());
        return [
            "project" => $project->getHash()
        ];
    }

    public function getModule(): string {
        return "timesheets";
    }
}
