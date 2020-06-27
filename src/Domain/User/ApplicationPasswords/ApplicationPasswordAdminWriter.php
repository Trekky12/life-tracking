<?php

namespace App\Domain\User\ApplicationPasswords;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ApplicationPasswordAdminWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ApplicationPasswordMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {
        return parent::save($id, $data, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'users_application_passwords_edit_admin';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id, "user" => $entry->user];
    }

    public function getModule(): string {
        return "general";
    }

}
