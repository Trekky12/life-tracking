<?php

namespace App\Domain\Recipes\Cookbook;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CookbookRemover extends ObjectActivityRemover {

    private $cookbook_service;
    
    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CookbookMapper $mapper, CookbookService $cookbook_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->cookbook_service = $cookbook_service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->cookbook_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'recipes_cookbooks_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "recipes";
    }

}
