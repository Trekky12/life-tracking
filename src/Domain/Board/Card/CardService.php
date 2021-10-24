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

class CardService extends Service {

    private $board_mapper;
    private $stack_service;
    private $label_service;
    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CardMapper $mapper, BoardMapper $board_mapper, StackService $stack_service, LabelService $label_service, UserService $user_service) {
        parent::__construct($logger, $user);
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

    public function getData($entry_id) {
        if (!$this->hasAccess($entry_id, [])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        $entry = $this->getEntry($entry_id);

        // append card labels and usernames to output
        $card_labels = $this->label_service->getLabelsFromCard($entry_id);
        $entry->labels = $card_labels;

        $users = $this->user_service->getAll();

        if ($entry->createdBy) {
            $entry->createdBy = $users[$entry->createdBy]->name;
        }
        if ($entry->changedBy) {
            $entry->changedBy = $users[$entry->changedBy]->name;
        }
        if ($entry->description) {
            $entry->description = html_entity_decode(htmlspecialchars_decode($entry->description));
        }
        if ($entry->title) {
            $entry->title = htmlspecialchars_decode($entry->title);
        }

        $response_data = ['entry' => $entry];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
