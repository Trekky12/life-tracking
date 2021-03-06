<?php

namespace App\Domain\User\ApplicationPasswords;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ApplicationPasswordAdminRemover extends ObjectActivityRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ApplicationPasswordMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        return parent::delete($id, $additionalData);
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
