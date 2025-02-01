<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Customer\CustomerMapper;

class SheetRemover extends ObjectActivityRemover {

    private $service;
    private $project_service;
    private $project_mapper;
    private $translation;
    private $customer_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        SheetService $service,
        SheetMapper $mapper,
        ProjectService $project_service,
        ProjectMapper $project_mapper,
        Translator $translation,
        CustomerMapper $customer_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
        $this->translation = $translation;
        $this->customer_mapper = $customer_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($project->id, $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        try {
            $series = $this->getMapper()->getSeriesSheets($project->id, $id);
            $series_ids = array_keys(
                array_map(function ($sheet) {
                    return $sheet->id;
                }, $series)
            );

            if (count($series) > 0) {

                // delete following
                $is_deletefollowing = is_array($additionalData) && array_key_exists("is_deletefollowing", $additionalData) && ($additionalData["is_deletefollowing"] == 1);

                if ($is_deletefollowing) {

                    $remaining_ids = $series_ids;
                    $index = array_search($id, $series_ids);

                    if ($index !== false) {
                        $remaining_ids = array_slice($series_ids, $index + 1);
                    }

                    // delete remainings
                    foreach ($remaining_ids as $following_id) {
                        $payload = parent::delete($following_id, $additionalData);
                        if (in_array($payload->getStatus(), [Payload::$STATUS_ERROR, Payload::$STATUS_DELETE_ERROR])) {
                            $this->logger->error("Delete of following entry failed " . $this->getMapper()->getDataObject(), array("id" => $id));
                            return new Payload(Payload::$STATUS_DELETE_ERROR, "ERROR");
                        }
                    }
                } else {
                    $entry = $this->getMapper()->get($id);

                    // if this is the base entry set the next entry 
                    // as reference for all following entries!
                    // also remove the reference of the first following entry
                    // as this is the new base
                    if ($entry->reference_sheet == null) {

                        // remove this entry from the series
                        $following_entries = array_values(array_diff($series_ids, [$entry->id]));

                        $update_reference = $this->getMapper()->updateReferenceSheet($id, $following_entries);
                        if ($update_reference) {
                            return parent::delete($id, $additionalData);
                        }

                        return new Payload(Payload::$STATUS_DELETE_ERROR, "ERROR");
                    }
                }
            }
        } catch (\Exception $ex) {
            return new Payload(Payload::$STATUS_ERROR, $ex->getMessage());
        }

        return parent::delete($id, $additionalData);
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
