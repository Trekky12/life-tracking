<?php

namespace App\Domain\Timesheets\NoticeField;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;

class NoticeFieldWriter extends ObjectActivityWriter {

    private $service;
    private $project_service;
    private $project_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, NoticeFieldService $service, NoticeFieldMapper $mapper, ProjectService $project_service, ProjectMapper $project_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($project->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['project'] = $project->id;

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        // set default category
        $this->setDefaultCategoryWhenNotSet($entry->id);

        return $payload;
    }

    public function getParentMapper() {
        return $this->project_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_noticefields_edit';
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

    private function setDefaultCategoryWhenNotSet($id) {
        $cat = $this->getMapper()->get($id);

        // Set all other non-default, since there can only be one default category
        if ($cat->is_default == 1) {
            $this->getMapper()->unset_default($id, $cat->type);
        }

        // when there is no default make this the default
        $default = $this->getMapper()->get_default($cat->type);
        if (is_null($default)) {
            $this->getMapper()->set_default($id, $cat->type);
        }
    }

}
