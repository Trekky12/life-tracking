<?php

namespace App\Board\Card;

class Card extends \App\Base\Model {
    
    static $MODEL_NAME = "MODEL_BOARDS_CARD";

    public function parseData(array $data) {

        $this->title = $this->exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING) : null;

        // new card --> save createdBy and hash
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
            $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : hash('CRC32', time() . rand(0, 1000000) . $this->title);
        }

        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : null;
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $this->stack = $this->exists('stack', $data) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        /**
         * Clean date/time
         */
        if (!empty($this->date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }

        if (!empty($this->time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
            $this->time = date('H:i:s');
        }

        if (empty($this->title)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\Interop\Container\ContainerInterface $ci) {
        return $this->title;
    }
    
    public function getParentID() {
        return $this->stack;
    }

}
