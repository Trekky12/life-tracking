<?php

namespace App\Domain\Board\Label;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardService;
use App\Application\Payload\Payload;

class LabelService extends Service {

    private $board_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, LabelMapper $mapper, BoardService $board_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->board_service = $board_service;
    }

    public function hasAccess($id, $data = []) {
        $user = $this->current_user->getUser()->id;
        $user_boards = $this->board_service->getBoardsOfUser($user);

        if (!is_null($id)) {
            $label = $this->mapper->get($id);
            if (!in_array($label->board, $user_boards)) {
                return false;
            }
        } elseif (is_array($data)) {
            if (!array_key_exists("board", $data) || !in_array($data["board"], $user_boards)) {
                return false;
            }
        }
        return true;
    }

    public function getLabelsFromCard($id) {
        return $this->mapper->getLabelsFromCard($id);
    }

    public function addLabelsToCard($id, $labels) {
        return $this->mapper->addLabelsToCard($id, $labels);
    }

    public function deleteLabelsFromCard($id) {
        return $this->mapper->deleteLabelsFromCard($id);
    }

    public function getLabelsFromBoard($board_id) {
        return $this->mapper->getLabelsFromBoard($board_id);
    }

    public function getData($entry_id) {
        if (!$this->hasAccess($entry_id, [])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        $entry = $this->getEntry($entry_id);
        if ($entry->name) {
            $entry->name = htmlspecialchars_decode($entry->name);
        }

        $response_data = ['entry' => $entry];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
