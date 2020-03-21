<?php

namespace App\Board\Stack;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\Board\BoardService;

class StackService extends \App\Base\Service {

    protected $dataobject = \App\Board\Stack\Stack::class;
    protected $dataobject_parent = \App\Board\Board::class;
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    private $board_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            BoardService $board_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->board_service = $board_service;
    }

    protected function getElementViewRoute($entry) {
        $board = $this->getParentObjectMapper()->getEntry($entry->getParentID());
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->board_service;
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

    public function archive($id, $archive) {
        $user = $this->current_user->getUser()->id;
        return $this->mapper->setArchive($id, $archive, $user);
    }

    public function move($stacks) {
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
    }

    public function getUserStacks($user_id) {
        return $this->mapper->getUserStacks($user_id);
    }

    public function getAll() {
        return $this->mapper->getAll();
    }

}
