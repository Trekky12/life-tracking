<?php

namespace App\Domain\Board;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class BoardRemover extends ObjectActivityRemover {

    private $board_service;
    
    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, BoardMapper $mapper, BoardService $board_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->board_service = $board_service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->board_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string {
        return 'boards_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "boards";
    }

}
