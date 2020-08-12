<?php

namespace App\Domain\Home\Widget;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class WidgetRemover extends ObjectActivityRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, WidgetMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'users_profile_frontpage';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "general";
    }

}
