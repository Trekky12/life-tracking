<?php

namespace App\Domain\Recipes\Ingredient;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class IngredientRemover extends ObjectActivityRemover {
    
    private $ingredient_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, IngredientMapper $mapper, IngredientService $ingredient_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->ingredient_service = $ingredient_service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->ingredient_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'recipes_ingredients_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "recipes";
    }

}
