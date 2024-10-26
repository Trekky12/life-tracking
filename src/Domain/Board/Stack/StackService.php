<?php

namespace App\Domain\Board\Stack;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardService;
use App\Domain\Board\BoardMapper;
use App\Domain\Board\Card\CardMapper;
use App\Application\Payload\Payload;

use App\Domain\Activity\ActivityCreator;

class StackService extends Service {

    private $board_service;
    private $board_mapper;
    private $card_mapper;
    private $activity_creator;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity_creator,
        StackMapper $mapper,
        BoardService $board_service,
        BoardMapper $board_mapper,
        CardMapper $card_mapper
    ) {
        parent::__construct($logger, $user);
        $this->activity_creator = $activity_creator;
        $this->mapper = $mapper;
        $this->board_mapper = $board_mapper;
        $this->board_service = $board_service;
        $this->card_mapper = $card_mapper;
    }

    public function hasAccess($id, $data = []) {
        $user = $this->current_user->getUser()->id;

        if (!is_null($id)) {
            $user_stacks = $this->mapper->getUserStacks($user);
            if (!in_array($id, $user_stacks)) {
                return false;
            }
        } elseif (is_array($data)) {
            $user_boards = $this->board_service->getBoardsOfUser($user);
            if (!array_key_exists("board", $data) || !in_array($data["board"], $user_boards)) {
                return false;
            }
        }
        return true;
    }

    public function archive($entry_id, $data) {

        if (!$this->hasAccess($entry_id, [])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (array_key_exists("archive", $data) && in_array($data["archive"], array(0, 1))) {

            $user = $this->current_user->getUser()->id;
            $is_archived = $this->mapper->setArchive($entry_id, $data["archive"], $user);


            $entry = $this->mapper->get($entry_id);
            $board = $this->board_mapper->get($entry->board);
            $link = [
                'route' => 'boards_view',
                'params' => ['hash' => $board->getHash()]
            ];
            $type = $data["archive"] == 0 ? 'unarchived' : 'archived';

            if (array_key_exists("cards", $data) && in_array($data["cards"], array(0, 1))) {

                $cards = $this->card_mapper->getCardIDsFromStack($entry_id, $data["archive"] == 0 ? 1 : 0);

                $is_archived_cards = $this->card_mapper->setArchiveByStack($entry_id, $data["archive"], $user);

                $is_archived = $is_archived && $is_archived_cards;

                if ($is_archived_cards) {
                    foreach ($cards as $card) {
                        $activity = $this->activity_creator->createChildActivity($type, "boards", $card["id"], $card["title"], $link, $this->board_mapper, $board->id);
                        $this->activity_creator->saveActivity($activity);
                    }
                }
            }

            if ($is_archived) {
                $activity = $this->activity_creator->createChildActivity($type, "boards", $entry_id, $entry->name, $link, $this->board_mapper, $board->id);
                $this->activity_creator->saveActivity($activity);
            }

            $response_data = ['is_archived' => $is_archived];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        } else {
            $response_data = ['status' => 'error', "error" => "missing data"];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
    }

    public function updatePosition($data) {

        if (array_key_exists("stack", $data) && !empty($data["stack"])) {
            $stacks = filter_var_array($data["stack"], FILTER_SANITIZE_NUMBER_INT);

            $user = $this->current_user->getUser()->id;
            $user_stacks = $this->mapper->getUserStacks($user);
            /**
             * Save new order
             * @see https://stackoverflow.com/a/15635201
             */
            foreach ($stacks as $position => $item) {
                if (in_array($item, $user_stacks)) {
                    $this->mapper->updatePosition($item, $position, $user);
                }
            }

            $response_data = ['status' => 'success'];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
        $response_data = ['status' => 'error'];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getUserStacks($user_id) {
        return $this->mapper->getUserStacks($user_id);
    }

    public function getAll() {
        return $this->mapper->getAll();
    }
}
