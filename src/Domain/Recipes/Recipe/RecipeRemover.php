<?php

namespace App\Domain\Recipes\Recipe;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class RecipeRemover extends ObjectActivityRemover {

    private $service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, RecipeMapper $mapper, RecipeService $service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $this->service->deleteImage($id);

        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'recipes_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "recipes";
    }

}
