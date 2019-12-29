<?php

namespace App\Board\Label;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Board\Label\Label';
    protected $parent_model = '\App\Board\Board';
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    private $board_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->board_mapper = new \App\Board\Mapper($this->ci);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, array &$data, Request $request) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_boards = $this->board_mapper->getElementsOfUser($user);

        if (!is_null($id)) {
            $label = $this->mapper->get($id);
            if (!in_array($label->board, $user_boards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        } elseif (is_array($data)) {
            if (!array_key_exists("board", $data) || !in_array($data["board"], $user_boards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    protected function afterGetAPI($id, $entry, Request $request) {

        if ($entry->name) {
            $entry->name = htmlspecialchars_decode($entry->name);
        }
        return $entry;
    }

    protected function getElementViewRoute($entry) {
        $board = $this->getParentObjectMapper()->get($entry->getParentID());
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->board_mapper;
    }

}
