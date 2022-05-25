<?php

namespace App\Domain\Board;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\User\UserService;
use App\Domain\Board\Stack\StackMapper;
use App\Domain\Board\Card\CardMapper;
use App\Domain\Board\Label\LabelMapper;
use App\Application\Payload\Payload;

class BoardService extends Service {

    private $user_service;
    private $stack_mapper;
    private $card_mapper;
    private $label_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, BoardMapper $mapper, UserService $user_service, StackMapper $stack_mapper, CardMapper $card_mapper, LabelMapper $label_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
        $this->stack_mapper = $stack_mapper;
        $this->card_mapper = $card_mapper;
        $this->label_mapper = $label_mapper;
    }

    public function getAllOrderedByName() {
        return $this->mapper->getUserItems('name');
    }

    public function view($hash, $sidebar = []) {

        $board = $this->getFromHash($hash);

        if (!$this->isMember($board->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $show_archive = SessionUtility::getSessionVar('show_archive', 0);

        $board_users = $this->getUsers($board->id);
        $users = $this->user_service->getUsersData($board_users);

        $labels = $this->label_mapper->getLabelsFromBoard($board->id);

        $data = [
            "board" => $board,
            "users" => $users,
            "labels" => $labels,
            "show_archive" => $show_archive,
            "sidebar" => $sidebar,
            "isBoardView" => true
        ];

        return new Payload(Payload::$RESULT_HTML, $data);
    }

    public function data($hash) {

        $board = $this->getFromHash($hash);

        if (!$this->isMember($board->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $board_users = $this->getUsers($board->id);

        $show_archive = true; //SessionUtility::getSessionVar('show_archive', 0);

        /**
         * Get stacks with cards
         */
        $stacks = $this->stack_mapper->getStacksFromBoard($board->id, $show_archive);

        $card_users = $this->card_mapper->getCardsUser();
        $card_labels = $this->label_mapper->getCardsLabel();

        foreach ($stacks as &$stack) {
            $stack->cards = $this->card_mapper->getCardsFromStack($stack->id, $card_users, $card_labels, $show_archive);
        }

        $users = $this->user_service->getUsersData($board_users);

        $labels = $this->label_mapper->getLabelsFromBoard($board->id);

        $data = [
            "stacks" => $stacks,
            "labels" => $labels,
            "users" => $users
        ];

        return new Payload(Payload::$RESULT_JSON, $data);
    }

    public function setArchive($data) {
        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1))) {
            SessionUtility::setSessionVar('show_archive', $data["state"]);
        }
        $response_data = ['status' => 'success'];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getBoardsOfUser($user) {
        return $this->mapper->getElementsOfUser($user);
    }

    public function index() {
        $boards = $this->getAllOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['boards' => $boards]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

}
