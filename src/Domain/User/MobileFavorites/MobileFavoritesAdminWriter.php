<?php

namespace App\Domain\User\MobileFavorites;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class MobileFavoritesAdminWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, MobileFavoritesMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $user = null): Payload {
        return parent::save($id, $data, $user);
    }

    public function getObjectViewRoute(): string {
        return 'users_mobile_favorites_edit_admin';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id, "user" => $entry->user];
    }

    public function getModule(): string {
        return "general";
    }

}
