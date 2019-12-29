<?php

namespace App\Board\Comment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {
    
    protected $model = '\App\Board\Comment\Comment';
    protected $parent_model = '\App\Board\Card\Card';
    protected $element_view_route = 'boards_view';
    protected $module = "boards";
    
    private $card_mapper;
    private $stack_mapper;
    private $board_mapper;
    
    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->card_mapper = new \App\Card\Mapper($this->ci);
        $this->stack_mapper = new \App\Stack\Mapper($this->ci);
        $this->board_mapper = new \App\Board\Mapper($this->ci);
    }
    
    /**
     * Does the user have access to this card?
     */
    protected function preSave($id, array $data, Request $request) {
        $user = $this->ci->get('helper')->getUser()->id;
        $user_cards = $this->board_mapper->getUserCards($user);
        
        if (!is_null($id)) {
            $comment = $this->mapper->get($id);
            if (!in_array($comment->card, $user_cards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }

        } elseif (is_array($data)) {
            if (!array_key_exists("card", $data) || !in_array($data["card"], $user_cards)) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }
    
    protected function getElementViewRoute($entry) {
        $card = $this->card_mapper->get($entry->getParentID());
        $stack = $this->stack_mapper->get($card->getParentID());
        $board = $this->board_mapper->get($stack->getParentID());
        $this->element_view_route_params["hash"] = $board->getHash();
        return parent::getElementViewRoute($entry);
    }
    
    protected function getParentObjectMapper() {
        return $this->card_mapper;
    }
    
}
