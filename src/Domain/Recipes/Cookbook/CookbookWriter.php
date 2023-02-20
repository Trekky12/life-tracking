<?php

namespace App\Domain\Recipes\Cookbook;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CookbookWriter extends ObjectActivityWriter {

    private $cookbook_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CookbookMapper $mapper, CookbookService $cookbook_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->cookbook_service = $cookbook_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        if ($this->cookbook_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        return $payload;
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
