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
        
        $set_password = array_key_exists('set_password', $data) ? filter_var($data['set_password'], FILTER_SANITIZE_STRING) : null;
        $set_password2 = array_key_exists('set_password2', $data) ? filter_var($data['set_password2'], FILTER_SANITIZE_STRING) : null;
        
        if ((!empty($set_password) || !empty($set_password2)) && $set_password !== $set_password2) {
            $this->logger->warning("Set Timesheet Password Failed, Passwords missmatch");
            $payload = new Payload(Payload::$STATUS_ERROR);
            $payload->addFlashMessage('additional_flash_message', $this->translation->getTranslatedString("PASSWORDSDONOTMATCH"));
            $payload->addFlashMessage('additional_flash_message_type', 'danger');
            return $payload;
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

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
