<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetService;

class SheetWriter extends ObjectActivityWriter {

    private $service;
    private $project_service;
    private $project_mapper;
    private $categorybudget_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        SheetMapper $mapper,
        SheetService $service,
        ProjectService $project_service,
        ProjectMapper $project_mapper,
        ProjectCategoryBudgetService $categorybudget_service
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
        $this->categorybudget_service = $categorybudget_service;
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

        $duration_modification = $project->has_duration_modifications && array_key_exists("duration_modification", $data) ? intval(filter_var($data["duration_modification"], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

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

        // Check Project category budget
        $sheet_categories = $this->mapper->getCategoriesFromSheet($entry->id);
        $budget_result = $this->categorybudget_service->checkCategoryBudgets($entry->project, $sheet_categories, $entry->id);

        foreach ($budget_result as $idx => $result) {
            $payload->addFlashMessage('additional_flash_message_type', $result["type"]);
            $payload->addFlashMessage('additional_flash_message', $result["message"]);
        }


        /**
         * Add repeating entries
         */
        if (
            array_key_exists("set_repeats", $data) && !empty($data["set_repeats"])
            && array_key_exists("repeat_count", $data) && !empty($data["repeat_count"])
            && array_key_exists("repeat_unit", $data) && !empty($data["repeat_unit"])
            && array_key_exists("repeat_multiplier", $data) && !empty($data["repeat_multiplier"])
        ) {
            $updated_entry = $this->service->getEntry($entry->id);

            $repeat_count = intval(filter_var($data["repeat_count"], FILTER_SANITIZE_NUMBER_INT));
            $repeat_multiplier = intval(filter_var($data["repeat_multiplier"], FILTER_SANITIZE_NUMBER_INT));
            $repeat_unit = filter_var($data["repeat_unit"], FILTER_SANITIZE_STRING);

            $start_date = new \DateTime($updated_entry->start);
            $end_date = new \DateTime($updated_entry->end);

            for ($i = 1; $i <= $repeat_count; $i++) {
                $new_entry = clone $updated_entry;
                $new_entry->id = null;
                $new_entry->reference_sheet = $entry->id;

                $new_start_date = clone $start_date;
                $new_end_date = clone $end_date;

                $interval = "P" . ($repeat_multiplier * $i) . strtoupper(substr($repeat_unit, 0, 1));

                $new_start_date->add(new \DateInterval($interval));
                $new_end_date->add(new \DateInterval($interval));

                $new_entry->start = $new_start_date->format('Y-m-d H:i:s');
                $new_entry->end = $new_end_date->format('Y-m-d H:i:s');

                $id = $this->insertEntry($new_entry);
            }
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
