<?php

namespace App\Domain\Board\Label;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Board\BoardService;

class LabelService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Board\Label\Label::class;
    protected $dataobject_parent = \App\Domain\Board\Board::class;
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

}
