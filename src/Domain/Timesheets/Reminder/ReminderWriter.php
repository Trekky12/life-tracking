<?php

namespace App\Domain\Timesheets\Reminder;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Notifications\Categories\NotificationCategoryService;
use App\Domain\Notifications\Categories\NotificationCategoryWriter;
use App\Domain\Main\Utility\Utility;

class ReminderWriter extends ObjectActivityWriter {

    private $service;
    private $project_service;
    private $project_mapper;
    private $notification_cat_service;
    private $notification_cat_writer;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        ReminderService $service,
        ReminderMapper $mapper,
        ProjectService $project_service,
        ProjectMapper $project_mapper,
        NotificationCategoryService $notification_cat_service,
        NotificationCategoryWriter $notification_cat_writer
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
        $this->notification_cat_service = $notification_cat_service;
        $this->notification_cat_writer = $notification_cat_writer;
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

        /**
         * Save messages
         */
        if(array_key_exists("messages", $data) && is_array($data["messages"])){

            $messages_preSave = array_map(function ($ex) {
                return $ex["id"];
            }, $this->mapper->getMessages($entry->id));

            $messages_afterSave = [];

            $messages_new = [];
            $messages_update = [];
            foreach ($data["messages"] as $idx => $msg) {

                $message_id = array_key_exists("id", $msg) && !empty($msg["id"]) ? intval(filter_var($msg["id"], FILTER_SANITIZE_NUMBER_INT)) : null;
                $message_message = array_key_exists("message", $msg) && !empty($msg["message"]) ? Utility::filter_string_polyfill($msg["message"]) : "";

                $message = ["id" => $message_id, "message" => $message_message];

                if (!is_null($message_id)) {
                    $messages_afterSave[] = $message_id;
                    $messages_update[] = $message;
                } else {
                    $messages_new[] = $message;
                }
            }

            // add new (id == null)
            if (!empty($messages_new)) {
                $send_count = $this->mapper->getMinSendCount($entry->id);
                $this->mapper->addMessages($entry->id, $messages_new, $send_count);
            }

            // update exercises
            if (!empty($messages_update)) {
                $this->mapper->updateMessages($entry->id, $messages_update);
            }

            // delete missing exercises
            $messages_removed = array_diff($messages_preSave, $messages_afterSave);
            if (!empty($messages_removed)) {
                $this->mapper->deleteMessages($entry->id, $messages_removed);
            }
        } else {
            $this->mapper->deleteMessages($entry->id);
        }


        /**
         * Save as notification category
         */
        $cat = $this->notification_cat_service->getCategoryByReminder($entry->id);
        if(is_null($cat)){
            $this->notification_cat_writer->save($cat, ["reminder" => $entry->id, "internal" => "1"]);
        }

        return $payload;
    }

    public function getParentMapper() {
        return $this->project_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_reminders_edit';
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
