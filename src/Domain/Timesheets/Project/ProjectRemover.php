<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ProjectRemover extends ObjectActivityRemover {

    private $project_service;
    
    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ProjectMapper $mapper, ProjectService $project_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->project_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_projects_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "timesheets";
    }

}
