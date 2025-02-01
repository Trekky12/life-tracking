<?php

namespace App\Domain;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;

abstract class ObjectActivityRemover extends ObjectRemover implements ObjectActivityData {

    protected $parent_mapper = null;
    private $activity_creator;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity_creator) {
        parent::__construct($logger, $user);
        $this->activity_creator = $activity_creator;
    }

    protected function deleteEntry($id) {
        $entry = $this->getMapper()->get($id);
        $activity = $this->activity_creator->createActivity("delete", $this->getModule(), $entry->id, $this->getMapper(), $this->getObjectLink($entry), $this->getParentMapper(), $this->getParentID($entry), $this->getAdditionalInformation($entry));
        $is_deleted = parent::deleteEntry($id);
        $this->activity_creator->saveActivity($activity);

        return $is_deleted;
    }

    public function getParentMapper() {
        return $this->parent_mapper;
    }

    public function getParentID($entry): ?int {
        return $entry->getParentID();
    }

    abstract function getModule(): string;

    abstract function getObjectViewRoute(): string;

    abstract function getObjectViewRouteParams($entry): array;

    public function getObjectLink($entry) {
        return ["route" => $this->getObjectViewRoute(), "params" => $this->getObjectViewRouteParams($entry)];
    }

    protected function getAdditionalInformation($entry): ?string {
        return null;
    }
}
