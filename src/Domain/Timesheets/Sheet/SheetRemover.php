<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;

class SheetRemover extends ObjectActivityRemover {

    private $project_service;
    private $project_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, SheetMapper $mapper, ProjectService $project_service, ProjectMapper $project_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->project_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_sheets';
    }

    public function getObjectViewRouteParams($entry): array {
        $project = $this->getParentMapper()->get($entry->getParentID());
        return [
            "project" => $project->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "timesheets";
    }

}
