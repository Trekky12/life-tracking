<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;

abstract class ObjectActivityWriter extends ObjectWriter implements ObjectActivityData {

    protected $parent_mapper = null;
    private $activity_creator;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity_creator) {
        parent::__construct($logger, $user);
        $this->activity_creator = $activity_creator;
    }

    public function getParentMapper() {
        return $this->parent_mapper;
    }

    protected function insertEntry($entry) {
        $id = parent::insertEntry($entry);
        $activity = $this->activity_creator->createActivity("create", $this->getModule(), $id, $this->getMapper(), $this->getObjectLink($id), $this->getParentMapper());
        $this->activity_creator->saveActivity($activity);

        return $id;
    }

    protected function updateEntry($entry) {
        $updated = parent::updateEntry($entry);
        $id = $entry->id;
        $activity = $this->activity_creator->createActivity("update", $this->getModule(), $id, $this->getMapper(), $this->getObjectLink($id), $this->getParentMapper());
        $this->activity_creator->saveActivity($activity);

        return $updated;
    }

    abstract function getModule(): string;

    abstract function getObjectViewRoute(): string;

    abstract function getObjectViewRouteParams(int $id): array;

    public function getObjectLink(int $id) {
        return ["route" => $this->getObjectViewRoute(), "params" => $this->getObjectViewRouteParams($id)];
    }

}
