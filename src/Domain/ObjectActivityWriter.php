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

    public function getParentID($entry): ?int {
        return $entry->getParentID();
    }

    protected function insertEntry($entry) : int{
        $id = parent::insertEntry($entry);
        if ($id) {
            $entry_new = $this->getMapper()->get($id);
            $activity = $this->activity_creator->createActivity("create", $this->getModule(), $entry_new->id, $this->getMapper(), $this->getObjectLink($entry_new), $this->getParentMapper(), $this->getParentID($entry_new), $this->getAdditionalInformation($entry_new));
            $this->activity_creator->saveActivity($activity);
        }

        return $id;
    }

    protected function updateEntry($entry) : bool {
        $updated = parent::updateEntry($entry);
        $entry_new = $this->getMapper()->get($entry->id);
        $activity = $this->activity_creator->createActivity("update", $this->getModule(), $entry_new->id, $this->getMapper(), $this->getObjectLink($entry_new), $this->getParentMapper(), $this->getParentID($entry_new), $this->getAdditionalInformation($entry_new));
        $this->activity_creator->saveActivity($activity);

        return $updated;
    }

    abstract function getModule(): string;

    abstract function getObjectViewRoute(): string;

    abstract function getObjectViewRouteParams($entry): array;

    public function getObjectLink($entry): array {
        return ["route" => $this->getObjectViewRoute(), "params" => $this->getObjectViewRouteParams($entry)];
    }

    protected function getAdditionalInformation($entry): ?string {
        return null;
    }
}
