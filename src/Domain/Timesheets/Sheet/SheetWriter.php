<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;

class SheetWriter extends ObjectActivityWriter {

    private $service;
    private $project_service;
    private $project_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, SheetMapper $mapper, SheetService $service, ProjectService $project_service, ProjectMapper $project_mapper) {
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

        $data['project'] = $project->id;

        // Do not save the notice
        $notice = $data["notice"];
        $data["notice"] = null;

        $duration_modification = $project->has_duration_modifications && array_key_exists("duration_modification", $data) && intval(filter_var($data["duration_modification"], FILTER_SANITIZE_NUMBER_INT)) > 0 ? true : false;

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        // Save the notice encrypted
        $this->mapper->setNotice($entry->id, $notice);

        $this->service->setDuration($entry, $project, $duration_modification);

        try {

            $this->mapper->deleteCategoriesFromSheet($id);
            if (array_key_exists("category", $data) && is_array($data["category"]) && !empty($data["category"])) {
                $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);

                $this->mapper->addCategoriesToSheet($entry->id, $categories);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error while saving categories at timesheet", array("data" => $id, "error" => $e->getMessage()));
        }

        return $payload;
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
