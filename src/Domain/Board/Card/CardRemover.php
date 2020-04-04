<?php

namespace App\Domain\Board\Card;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardMapper;
use App\Application\Payload\Payload;

class CardRemover extends ObjectActivityRemover {

    private $card_service;
    private $board_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CardMapper $mapper, CardService $card_service, BoardMapper $board_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->card_service = $card_service;
        $this->board_mapper = $board_mapper;
    }

    public function delete($id, $user = null): Payload {
        if (!$this->card_service->hasAccess($id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, null);
    }

    public function getParentMapper() {
        return $this->board_mapper;
    }

    public function getParentID($entry): int {
        return $this->mapper->getCardBoard($entry->id);
    }

    public function getObjectViewRoute(): string {
        return 'boards_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $board_id = $this->mapper->getCardBoard($entry->id);
        $board = $this->board_mapper->get($board_id);
        return ["hash" => $board->getHash()];
    }

    public function getModule(): string {
        return "boards";
    }

}
