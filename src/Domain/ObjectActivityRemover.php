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
        $activity = $this->activity_creator->createActivity("delete", $this->getModule(), $id, $this->getMapper(), $this->getObjectLink($id), $this->getParentMapper());
        $is_deleted = parent::deleteEntry($id);
        $this->activity_creator->saveActivity($activity);

        return $is_deleted;
    }

    public function getParentMapper() {
        return $this->parent_mapper;
    }

    abstract function getModule(): string;

    abstract function getObjectViewRoute(): string;

    abstract function getObjectViewRouteParams(int $id): array;

    public function getObjectLink(int $id) {
        return ["route" => $this->getObjectViewRoute(), "params" => $this->getObjectViewRouteParams($id)];
    }

}
