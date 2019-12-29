<?php

namespace App\Board\Comment;

class Comment extends \App\Base\Model {
    
    static $MODEL_NAME = "MODEL_BOARDS_COMMENT";

    public function parseData(array $data) {

        $this->card = $this->exists('card', $data) ? filter_var($data['card'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->comment = $this->exists('comment', $data) ? filter_var($data['comment'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        if (empty($this->comment)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\Interop\Container\ContainerInterface $ci) {
        return $this->comment;
    }

    public function getParentID() {
        return $this->card;
    }

}
