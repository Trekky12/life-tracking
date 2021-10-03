<?php

namespace App\Domain\Timesheets\ProjectCategoryBudget;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;

class ProjectCategoryBudgetWriter extends ObjectActivityWriter {

    private $project_service;
    private $project_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ProjectCategoryBudgetMapper $mapper, ProjectService $project_service, ProjectMapper $project_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        

        $data['project'] = $project->id;

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        try {

            $this->mapper->deleteCategoriesFromCategoryBudget($id);
            if (array_key_exists("category", $data) && is_array($data["category"]) && !empty($data["category"])) {
                $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);

                $this->mapper->addCategoriesToCategoryBudget($entry->id, $categories);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error while saving categories at timesheet categories budget", array("data" => $id, "error" => $e->getMessage()));
        }

        return $payload;
    }

    public function getParentMapper() {
        return $this->project_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_project_categorybudget_edit';
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
