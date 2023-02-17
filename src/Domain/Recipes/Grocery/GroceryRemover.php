<?php

namespace App\Domain\Recipes\Grocery;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class GroceryRemover extends ObjectActivityRemover {
    
    private $grocery_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, GroceryMapper $mapper, GroceryService $grocery_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->grocery_service = $grocery_service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->grocery_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'recipes_groceries_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "recipes";
    }

}
