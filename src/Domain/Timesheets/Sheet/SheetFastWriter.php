<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;

class SheetFastWriter extends ObjectActivityWriter {

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

    public function fastCheckIn($hash, $data) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        $entry_id = $this->createCheckInEntry($project, $data);

        if (array_key_exists("category", $data) && is_array($data["category"]) && !empty($data["category"])) {
            $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);

            $this->mapper->addCategoriesToSheet($entry_id, $categories);
        }

        $result = ["status" => "success", "data" => 0];
        $result["data"] = !is_null($entry_id) ? 1 : 0;

        return new Payload(Payload::$RESULT_JSON, $result);
    }

    private function createCheckInEntry($project, $data) {
        // always create new entry with current timestamp
        $data["start"] = date('Y-m-d H:i');
        $data["project"] = $project->id;
        $data["user"] = $this->current_user->getUser()->id;

        $entry = $this->createEntry($data);
        return $this->insertEntry($entry);
    }

    public function fastCheckOut($hash, $data) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        // get a existing entry for today with start but without end
        $entry = $this->service->getLastSheetWithStartDateToday($project->id);
        if (!is_null($entry)) {
            $this->updateEntryForCheckOut($entry, $data);
            $this->service->setDuration($entry, $project);
            $entry_id = $entry->id;
        } else {
            // otherwise create new entry               
            $entry_id = $this->createCheckOutEntry($project, $data);
        }
        
        if (array_key_exists("category", $data) && is_array($data["category"]) && !empty($data["category"])) {
            $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);

            $this->mapper->addCategoriesToSheet($entry_id, $categories);
        }

        $result = ["status" => "success", "data" => 0];
        $result["data"] = !is_null($entry) ? 1 : 0;

        return new Payload(Payload::$RESULT_JSON, $result);
    }

    private function createCheckOutEntry($project, $data) {
        // always create new entry with current timestamp
        $data["end"] = date('Y-m-d H:i');
        $data["project"] = $project->id;
        $data["user"] = $this->current_user->getUser()->id;

        $entry = $this->createEntry($data);
        return $this->insertEntry($entry);
    }

    private function updateEntryForCheckOut($entry, $data) {
        $entry->end = date('Y-m-d H:i');

        // parse lat/lng/acc values from post data
        $objectName = $this->getMapper()->getDataObject();
        $dataObject = new $objectName($data);

        $entry->end_lat = $dataObject->end_lat;
        $entry->end_lng = $dataObject->end_lng;
        $entry->end_acc = $dataObject->end_acc;

        $this->updateEntry($entry);
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
