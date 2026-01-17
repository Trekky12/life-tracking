<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Translator;
use App\Application\Payload\Payload;

class ProjectWriter extends ObjectActivityWriter {

    private $project_service;
    private $translation;

    public function __construct(LoggerInterface $logger, 
            CurrentUser $user, 
            ActivityCreator $activity, 
            ProjectMapper $mapper, 
            ProjectService $project_service,
            Translator $translation) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->translation = $translation;
    }

    public function save($id, $data, $additionalData = null): Payload {

        if ($this->project_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $calendarView = [];
        if(!is_null($id)){
            $users_preSave = $this->mapper->getUsers($id);
            foreach($users_preSave as $user_id => $username){
                $calendarView[$user_id] = $this->mapper->getCalendarViewAndDate($id, $user_id);
            }
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        if(!empty($calendarView)){
            $users_afterSave = $this->mapper->getUsers($id);
            foreach($users_afterSave as $user_id => $username){
                if(array_key_exists($user_id, $calendarView)){
                    $this->mapper->setCalendarViewAndDate($id, $user_id, $calendarView[$user_id]);
                }
            }
        }

        return $payload;
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
