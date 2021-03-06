<?php

namespace App\Domain\Notifications\Categories;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class NotificationCategoryRemover extends ObjectActivityRemover {
    
    private $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, NotificationCategoryMapper $mapper, NotificationCategoryService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
    }

    public function delete($id, $additionalData = null): Payload {
        if (!is_null($id) && $this->service->isInternalCategory($id)) {
            return new Payload(Payload::$NO_ACCESS);
        }
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'notifications_categories_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "notifications";
    }

}
