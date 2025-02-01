<?php

namespace App\Domain\Board\Card;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardMapper;
use App\Domain\Board\Stack\StackService;
use App\Domain\Board\Label\LabelService;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;
use App\Domain\Activity\ActivityCreator;

class CardService extends Service {

    private $board_mapper;
    private $stack_service;
    private $label_service;
    private $user_service;
    private $activity_creator;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity_creator,
        CardMapper $mapper,
        BoardMapper $board_mapper,
        StackService $stack_service,
        LabelService $label_service,
        UserService $user_service
    ) {
        parent::__construct($logger, $user);
        $this->activity_creator = $activity_creator;
        $this->mapper = $mapper;
        $this->board_mapper = $board_mapper;
        $this->stack_service = $stack_service;
        $this->label_service = $label_service;
        $this->user_service = $user_service;
    }

    public function hasAccess($id, $data = []) {
        $user = $this->current_user->getUser()->id;

        if (!is_null($id)) {
            $user_cards = $this->mapper->getUserCards($user);
            if (!in_array($id, $user_cards)) {
                return false;
            }
        } elseif (is_array($data)) {
            $user_stacks = $this->stack_service->getUserStacks($user);
            if (!array_key_exists("stack", $data) || !in_array($data["stack"], $user_stacks)) {
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

            if ($is_archived) {
                $entry = $this->mapper->get($entry_id);

                $board_id = $this->mapper->getCardBoard($entry_id);
                $board = $this->board_mapper->get($board_id);
                $link = [
                    'route' => 'boards_view',
                    'params' => ['hash' => $board->getHash()]
                ];
                $type = $data["archive"] == 0 ? 'unarchived' : 'archived';
                $activity = $this->activity_creator->createChildActivity($type, "boards", $entry_id, $entry->title, $link, $this->board_mapper, $board->id, \App\Domain\Board\Card\Card::class);
                $this->activity_creator->saveActivity($activity);
            }

            $response_data = ['is_archived' => $is_archived];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        } else {
            $response_data = ['status' => 'error', "error" => "missing data"];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
    }

    public function moveCard($data) {

        $stack = array_key_exists("stack", $data) && !empty($data["stack"]) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;
        $card = array_key_exists("card", $data) && !empty($data["card"]) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;
        $position = array_key_exists("position", $data) && !empty($data["position"]) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : null;

        $user = $this->current_user->getUser()->id;
        $user_cards = $this->mapper->getUserCards($user);
        $user_stacks = $this->stack_service->getUserStacks($user);

        $response_data = ['status' => 'error'];
        if (!is_null($stack) && !is_null($card) && in_array($stack, $user_stacks) && in_array($card, $user_cards)) {
            $this->mapper->moveCard($card, $stack, $position, $user);

            $response_data = ['status' => 'success'];
        }

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function updatePosition($data) {

        if (array_key_exists("card", $data) && !empty($data["card"])) {

            $cards = filter_var_array($data["card"], FILTER_SANITIZE_NUMBER_INT);

            $user = $this->current_user->getUser()->id;
            $user_cards = $this->mapper->getUserCards($user);

            foreach ($cards as $position => $item) {
                if (in_array($item, $user_cards)) {
                    $this->mapper->updatePosition($item, $position, $user);
                }
            }

            $response_data = ['status' => 'success'];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }
        $response_data = ['status' => 'error'];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function getCardsFromStack($stack_id, $show_archive) {
        return $this->mapper->getCardsFromStack($stack_id, $show_archive);
    }

    public function getCardsUser() {
        return $this->mapper->getCardsUser();
    }
}
