<?php

namespace App\Board\Comment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    
    public function init() {
        $this->model = '\App\Board\Comment\Comment';
        
        $this->mapper = new Mapper($this->ci);
        $this->board_mapper = new \App\Board\Mapper($this->ci);
    }
    
    /**
     * Does the user have access to this card?
     */
    protected function preSave($id, $data) {
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
    
}
