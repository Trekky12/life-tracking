<?php

namespace App\Domain\Board\Label;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardMapper;
use App\Application\Payload\Payload;

class LabelRemover extends ObjectActivityRemover {

    private $label_service;
    private $board_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, LabelMapper $mapper, LabelService $label_service, BoardMapper $board_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->label_service = $label_service;
        $this->board_mapper = $board_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        if (!$this->label_service->hasAccess($id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->board_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'boards_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $board = $this->getParentMapper()->get($entry->getParentID());
        return ["hash" => $board->getHash()];
    }

    public function getModule(): string {
        return "boards";
    }

}
