<?php

namespace App\Domain\Timesheets\Reminder;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Customer\CustomerService;

class ReminderService extends Service {

    private $project_service;
    protected $customer_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ReminderMapper $mapper,
        ProjectService $project_service,
        CustomerService $customer_service
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->customer_service = $customer_service;
    }

    public function index($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $reminders = $this->mapper->getFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, [
            'reminders' => $reminders,
            "project" => $project
        ]);
    }

    public function edit($hash, $entry_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($project->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        $messages = [];

        if(!is_null($entry)){
            $messages = $this->mapper->getMessages($entry_id);
        }

        return new Payload(Payload::$RESULT_HTML, [
            "entry" => $entry,
            "project" => $project,
            "messages" => $messages
        ]);
    }

    public function getRemindersByProject(){
        return $this->mapper->getRemindersByProject();
    }

    public function wasNotificationSent(int $project, int $reminder, ?int $timesheet = null){
        return $this->mapper->wasNotificationSent($project, $reminder, $timesheet);
    }

    public function markAsSent(int $project, int $reminder, int $message, ?int $timesheet = null){
        $this->mapper->markAsSent($project, $reminder, $timesheet);

        $this->mapper->addMessageSent($message);
    }

    public function getNextMessage($reminder_id) {
        return $this->mapper->getNextMessage($reminder_id);
    }

}
