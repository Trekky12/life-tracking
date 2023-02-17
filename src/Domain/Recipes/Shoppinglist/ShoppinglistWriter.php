<?php

namespace App\Domain\Recipes\Shoppinglist;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class ShoppinglistWriter extends ObjectActivityWriter {

    private $shoppinglist_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, ShoppinglistMapper $mapper, ShoppinglistService $shoppinglist_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->shoppinglist_service = $shoppinglist_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        if ($this->shoppinglist_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        return $payload;
    }

    public function getObjectViewRoute(): string {
        return 'recipes_shoppinglists_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "recipes";
    }

}
