<?php

namespace App\Domain\Board\Stack;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardMapper;
use App\Application\Payload\Payload;

class StackWriter extends ObjectActivityWriter {

    private $stack_service;
    private $board_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, StackMapper $mapper, StackService $stack_service, BoardMapper $board_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->stack_service = $stack_service;
        $this->board_mapper = $board_mapper;
    }

    public function save($id, $data, $user = null): Payload {
        if (!$this->stack_service->hasAccess($id, $data)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::save($id, $data, $user);
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
