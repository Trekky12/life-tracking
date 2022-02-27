<?php

namespace App\Domain\Board\Stack;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardService;
use App\Application\Payload\Payload;

class StackService extends Service {

    private $board_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, StackMapper $mapper, BoardService $board_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->board_service = $board_service;
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
