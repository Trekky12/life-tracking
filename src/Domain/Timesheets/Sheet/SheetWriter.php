<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetService;
use App\Domain\Main\Utility\Utility;
use App\Domain\Timesheets\Customer\CustomerMapper;

class SheetWriter extends ObjectActivityWriter {

    private $service;
    private $project_service;
    private $project_mapper;
    private $categorybudget_service;
    private $translation;
    private $customer_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        SheetMapper $mapper,
        SheetService $service,
        ProjectService $project_service,
        ProjectMapper $project_mapper,
        ProjectCategoryBudgetService $categorybudget_service,
        Translator $translation,
        CustomerMapper $customer_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
        $this->categorybudget_service = $categorybudget_service;
        $this->translation = $translation;
        $this->customer_mapper = $customer_mapper;
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

        $updated_entry = $this->service->getEntry($entry->id);

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

        $categories = count($this->getMapper()->getCategoriesFromSheet($entry->id));
        if ($categories == 0) {
            $payload->addFlashMessage('additional_flash_message_type', 'warning');
            $payload->addFlashMessage('additional_flash_message', $this->translation->getTranslatedString("TIMESHEETS_WARNING_NO_CATEGORY_ASSIGNED"));
        }


        /**
         * Add repeating entries
         */
        if (
            array_key_exists("set_repeats", $data) && !empty($data["set_repeats"])
            && array_key_exists("repeat_count", $data) && !empty($data["repeat_count"])
        ) {
            $repeat_count = intval(filter_var($data["repeat_count"], FILTER_SANITIZE_NUMBER_INT));

            $start_date = new \DateTime($updated_entry->start ?? '');
            $end_date = new \DateTime($updated_entry->end ?? '');

            for ($i = 1; $i <= $repeat_count; $i++) {
                $new_entry = clone $updated_entry;
                $new_entry->id = null;
                $new_entry->reference_sheet = !is_null($entry->reference_sheet) ? $entry->reference_sheet : $entry->id;

                $new_start_date = clone $start_date;
                $new_end_date = clone $end_date;

                $interval = "P" . (intval($new_entry->repeat_multiplier) * $i) . strtoupper(substr($new_entry->repeat_unit, 0, 1));

                $new_start_date->add(new \DateInterval($interval));
                $new_end_date->add(new \DateInterval($interval));

                $new_entry->start = $new_start_date->format('Y-m-d H:i:s');
                $new_entry->end = $new_end_date->format('Y-m-d H:i:s');

                // Check if this sheet already exists and create only appended items
                $sheet_exists = $this->mapper->hasEqualSheet($new_entry);

                if (!$sheet_exists) {
                    $id = $this->insertEntry($new_entry);
                }
            }
        }

        /**
         * Update remaining sheets?
         */

        if (array_key_exists("action", $data) && !empty($data["action"])) {

            $action = Utility::filter_string_polyfill($data["action"]);

            if ($action == "update_following") {

                $series = $this->getMapper()->getSeriesSheets($project->id, $id);
                $series_ids = array_keys(
                    array_map(function ($sheet) {
                        return $sheet->id;
                    }, $series)
                );
                $remaining_sheets = $series;
                $index = array_search($id, $series_ids);
                if ($index !== false) {
                    $remaining_sheets = array_slice($series, $index + 1);
                }

                // Update remainings

                $start_date = new \DateTime($updated_entry->start ?? '');
                $end_date = new \DateTime($updated_entry->end ?? '');
                $repeat_unit = $updated_entry->repeat_unit;
                $repeat_multiplier = $updated_entry->repeat_multiplier;

                foreach ($remaining_sheets as $sheet) {
                    $this->logger->error("Updating the following entry " . $this->getMapper()->getDataObject(), array("id" => $id));

                    $interval = "P" . (intval($repeat_multiplier)) . strtoupper(substr($repeat_unit, 0, 1));

                    $start_date->add(new \DateInterval($interval));
                    $end_date->add(new \DateInterval($interval));

                    $sheet->start = $start_date->format('Y-m-d H:i:s');
                    $sheet->end = $end_date->format('Y-m-d H:i:s');
                    $sheet->duration =  $updated_entry->duration;
                    $sheet->duration_modified =  $updated_entry->duration_modified;
                    $sheet->start_lat =  $updated_entry->start_lat;
                    $sheet->start_lng =  $updated_entry->start_lng;
                    $sheet->start_acc =  $updated_entry->start_acc;
                    $sheet->end_lat =  $updated_entry->end_lat;
                    $sheet->end_lng =  $updated_entry->end_lng;
                    $sheet->end_acc =  $updated_entry->end_acc;
                    $sheet->is_invoiced =  $updated_entry->is_invoiced;
                    $sheet->is_billed =  $updated_entry->is_billed;
                    $sheet->is_payed =  $updated_entry->is_payed;
                    $sheet->is_happened =  $updated_entry->is_happened;
                    $sheet->customer =  $updated_entry->customer;

                    $update = $this->updateEntry($sheet);

                    $repeat_unit = $sheet->repeat_unit;
                    $repeat_multiplier = $sheet->repeat_multiplier;
                }
            }
        }

        /**
         * start/end modify
         */
        if (
            array_key_exists("set_date_modified", $data) && !empty($data["set_date_modified"])
            && array_key_exists("start_modified", $data) && !empty($data["start_modified"])
        ) {

            $start_modified = Utility::filter_string_polyfill($data['start_modified']);
            $end_modified = null;

            if (!is_null($updated_entry->start) && !is_null($updated_entry->end)) {
                $start_date = new \DateTime($updated_entry->start);
                $end_date = new \DateTime($updated_entry->end);

                $start_date_modified = new \DateTime($start_modified);
                $end_date_modified = clone $start_date_modified;
                $end_date_modified->add($start_date->diff($end_date));
                $end_modified = $end_date_modified->format('Y-m-d H:i:s');
            }

            $this->mapper->set_start_end_modified($entry->id, $start_modified, $end_modified);
        } else {
            if (!is_null($updated_entry->start_modified) || !is_null($updated_entry->end_modified)) {
                $this->mapper->set_start_end_modified($entry->id, null, null);
            }
        }

        return $payload;
    }

    public function getParentMapper() {
        return $this->project_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_sheets_edit';
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

    protected function getAdditionalInformation($entry): ?string {
        if ($entry->customer) {
            $project = $this->getParentMapper()->get($entry->getParentID());
            $customerDescription = $project->customers_name_singular ? $project->customers_name_singular : $this->translation->getTranslatedString("TIMESHEETS_CUSTOMER");

            $customer = $this->customer_mapper->get($entry->customer);

            return sprintf("%s: %s", $customerDescription, $customer->name);
        }
        return parent::getAdditionalInformation($entry);
    }
}
